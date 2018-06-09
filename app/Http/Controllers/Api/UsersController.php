<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Image;
use App\Models\UserBaseInfo;
use App\Models\UserTargetInfo;
use App\Transformers\UserBaseInfoTransformer;
use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Http\Requests\Api\UserRequest;

class UsersController extends Controller
{
    public function weappStore(UserRequest $request)
    {
        // 缓存中是否存在对应的 key
        $verifyData = \Cache::get($request->verification_key);

        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }

        // 判断验证码是否相等，不相等反回 401 错误
        if (!hash_equals((string)$verifyData['code'], $request->verification_code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        // 获取微信的 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($request->code);

        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code 不正确');
        }

        // 如果 openid 对应的用户已存在，报错403
        $user = User::where('weapp_openid', $data['openid'])->first();

        if ($user) {
            return $this->response->errorForbidden('微信已绑定其他用户，请直接登录');
        }

        // 创建用户
        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
            'weapp_openid' => $data['openid'],
            'weixin_session_key' => $data['session_key'],
        ]);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        // meta 中返回 Token 信息
        return $this->response->item($user, new UserTransformer())
            ->setMeta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
            ])
            ->setStatusCode(201);
    }

    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);

        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }

        if (!hash_equals((string)$verifyData['code'], $request->verification_code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        return $this->response->item($user, new UserTransformer())
            ->setMeta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
            ])
            ->setStatusCode(201);
    }

    public function me()
    {
        return $this->response->item($this->user(), new UserTransformer());
    }

    public function update(UserRequest $request)
    {
        $user = $this->user();

        $attributes = $request->only(['name', 'email', 'introduction', 'registration_id']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);

            $attributes['avatar'] = $image->path;
        }
        $user->update($attributes);

        return $this->response->item($user, new UserTransformer());
    }

    public function activedIndex(User $user)
    {
        return $this->response->collection($user->getActiveUsers(), new UserTransformer());
    }

    public function match(Request $request)
    {
        $user1 = $this->user();
        $user1_target_info = $this->user()->targetinfo()->get()[0];//获取UserTargetInfo模型
        $user1_base_info = $this->user()->baseinfo()->get()[0];//获取UserBaseInfo模型

        $users_base_infos = UserBaseInfo::where('user_id', '!=', $user1->id)->get(); //此处不直接一并取出user1而用下面这种方法，是为了省去大量判断，便于定位user1的位置单独处理
        $users_target_infos = UserTargetInfo::where('user_id', '!=', $user1->id)->get(); //此处不直接一并取出user1而用下面这种方法，是为了省去大量判断，便于定位user1的位置单独处理

        $users_base_infos[] = $user1_base_info; //将user1的模型对象添加到其他模型对象数组末尾，方便一起进行处理
        $users_target_infos[] = $user1_target_info; //将user1的模型对象添加到其他模型对象数组末尾，方便一起进行处理

        $info_array = $this->processInfo($users_base_infos);
        $base_info_1 = $info_array['info_1']; //当前用户基本信息，一维数组
        $base_info = $info_array['info'];     //其他用户基本信息，二维数组

        $info_array = $this->processInfo($users_target_infos);
        $target_info_1 = $info_array['info_1'];//当前用户目标信息，一维数组
        $target_info = $info_array['info'];//其他用户目标信息，二维数组

        $match_user1_res = array(); //记录其他用户基本信息与user1目标信息的匹配结果的数组
        $match_other_res = array(); //记录user1基本信息与其他用户目标信息的匹配结果的数组

        for ($i = 0; $i < count($base_info); $i++) {
            $match_user1_res[$i] = 0;
            $match_other_res[$i] = 0;
            foreach ($target_info_1 as $key => $value) {
                if ($value == $base_info[$i][$key]) {
                    $match_user1_res[$i]++;
                }
            }
        }


        dd($match_user1_res);
        $match_user = User::find(3)->baseInfo()->get();
        return $this->response->item($match_user, new UserBaseInfoTransformer());
    }

    /**
     * Notes:
     * @param $user_infos :UserBaseInfo或UserTargetInfo模型对象
     * 处理对象数组，返回属性->值键值对数组，包括当前用户和其他用户两个数组
     * @return array
     */
    public function processInfo($user_infos)
    {
        $k = 0; //控制数组长度，即计算有多少组数据
        foreach ($user_infos as $user) {     //依次获取UserBaseInfo模型
            $attribute_array = $user->getFillable();//获取UserTargetInfo模型所有属性数组
            for ($i = 0; $i < count($attribute_array); $i++) { //不需要获取user_id，name,phone,因此从第二个属性开始获取
                $attribute = $attribute_array[$i];
                if ($k == count($user_infos) - 1) { //单独处理user1的基本信息，不可与其他数据一并处理，否则会出现user1匹配到user1的严重逻辑错误
                    $info_1[$attribute] = $user->$attribute;
                    continue;
                }
                $info[$k][$attribute] = $user->$attribute;
            }
            $k++;
        };
        return array(
            'info_1' => $info_1, //当前用户相关信息，一维数组
            'info' => $info  //其他用户相关信息，二维数组
        );
    }
}
