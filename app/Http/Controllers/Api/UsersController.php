<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AwaitMatchUserRequest;
use App\Http\Requests\Api\MatchUserRequest;
use App\Models\User;
use App\Models\Image;
use App\Models\UserAwaitMatchInfo;
use App\Models\UserBaseInfo;
use App\Models\UserMatchInfo;
use App\Models\UserTargetInfo;
use App\Transformers\UserAwaitMatchInfoTransformer;
use App\Transformers\UserBaseInfoTransformer;
use App\Transformers\UserMatchInfoTransformer;
use App\Transformers\UserTransformer;
use App\Http\Requests\Api\UserRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    /**
     * Notes:进行研友匹配度相似度计算，返回由推荐用户基本信息构成的数组
     */
    public function matchUsersShow()
    {
        //各属性值的权值
        $weight = array(
            'sex' => 8,
            'hometown' => 2,
            'area' => 3,
            'school_place' => 4,
            'school_name' => 5,
            'school_field' => 9,
            'school_type' => 6,
            'study_style' => 10,
            'good_subject' => 7
        );

//        $sum_upper=40;  //匹配度之和的下限，用于过滤互相匹配度均较低的情况
//        $diff_lower=15;  //匹配度之差的上限，用于过滤单方匹配度较高的情况
        $user1 = $this->user();
        $user1_target_info = $this->user()->targetinfo()->get()->first();//获取UserTargetInfo模型
        $user1_base_info = $this->user()->baseinfo()->get()->first();//获取UserBaseInfo模型


        /**这两个查询语句相当于
         * SELECT * FROM user_base_infos
         * WHERE user_id != $user1->id and
         * WHERE Not EXISTS (SELECT * from user_await_match_infos
         * where user1_id = 32 and state = false and user2_id = user_base_infos.user_id)
         * 目的是从表中筛选出不符合匹配条件(即黑名单),方法是从待匹配表中找出当前用户的所有匹配记录中状态为false的user2_id，并从查询结果中去除这些id
         * 此处不直接一并取出user1而用下面这种方法，是为了省去大量判断，便于定位user1的位置单独处理
         **/
        $users_base_infos = UserBaseInfo::where('user_id', '!=', $user1->id)
            ->whereNotExists(function ($query) use ($user1) {
                $query->select(DB::raw(1))
                    ->from('user_await_match_infos')
                    ->where('user1_id', $user1->id)->whereraw('state = false and user2_id = user_base_infos.user_id');
            })->get();

        $users_target_infos = UserTargetInfo::where('user_id', '!=', $user1->id)
            ->whereNotExists(function ($query) use ($user1) {
                $query->select(DB::raw(1))
                    ->from('user_await_match_infos')
                    ->where('user1_id', $user1->id)->whereraw('state = false and user2_id = user_target_infos.user_id');
            })->get();

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
            $user2_id = $base_info[$i]['user_id'];
            $match_user1_res[$user2_id] = 0;
            foreach ($target_info_1 as $key => $value) {
                if ($value == $base_info[$i][$key] || $value == '不介意') {
                    $match_user1_res[$user2_id] += $weight[$key]; //记录“user_id“与其base_info跟user1_target_info的”匹配程度”所组成的键值对数组
                }
            }
        }

        for ($i = 0; $i < count($target_info); $i++) {
            $user2_id = $target_info[$i]['user_id'];
            $match_other_res[$user2_id] = 0;
            foreach ($target_info[$i] as $key => $value) {
                if ($value == $base_info_1[$key] || $value == '不介意') {
                    $match_other_res[$user2_id] += $weight[$key]; //记录“user_id“与其$target_info跟user1_base_info的”匹配程度”所组成的键值对数组
                }
            }
        }

        //计算A和B之间的匹配度的调和平均值，即相似度
        foreach ($match_user1_res as $key => $value) {
            $match_res_tiaohe[$key] = round(2 * $value * $match_other_res[$key] / ($value + $match_other_res[$key]), 2);
        }

//        foreach ($match_user1_res as $key => $value) {
//            $match_res_sum[$key] = $value + $match_other_res[$key];   //计算匹配度之和，用于表示两者互相的匹配程度
//            $match_res_diff[$key] = abs($value - $match_other_res[$key]);  //计算匹配度只差，用于表示两者匹配度是否互相接近
//        }
//
//
//        //过滤互相匹配度均较低的情况
//        $array1 = array_where($match_res_sum, function ($value, $key) use ($sum_upper) {
//            return $value>=$sum_upper;
//        });
//
//        //匹配度之差的上限，用于过滤单方匹配度较高的情况
//        $array2 = array_where($match_res_diff, function ($value, $key) use ($diff_lower) {
//            return $value<=$diff_lower;
//        });

//
//        foreach ($array1 as $key => $value) {
//            if (isset($array2[$key])) {
//                $match_res[$key] = $match_user1_res[$key];
//            }
//        }

        //按照相似度降序排序，取出前五名
        arsort($match_res_tiaohe);
        $match_res = array_slice($match_res_tiaohe, 0, 5, true);
        $match_user = new Collection();
        foreach ($match_res as $key => $value) {
            $match_user->push(User::find($key)->baseInfo()->get()->first());
        }
        return $this->response->item($match_user, new UserBaseInfoTransformer());
    }

    /**
     * Notes:接受邀请，进行匹配，存入数据库
     * 难点在于安全检测，理清两个表中的user1和user2的对应关系
     * 正确情况下，在Await表中，传入的user2_id参数应为user1_id字段，即邀请人
     * 当前用户应为Await表中user2_id字段，即被邀请人
     * 而在Match表中，当前用户为user1，即被邀请人
     * 传入的参数为user2，即邀请人
     * @param MatchUserRequest $matchUserRequest
     * @param UserMatchInfo $userMatchInfo
     * @return \Dingo\Api\Http\Response
     */
    public function matchUsersStore(MatchUserRequest $matchUserRequest, UserMatchInfo $userMatchInfo)
    {
        $user1_id = $this->user()->id; //当前接受邀请的用户id
        $user2_id = (int)$matchUserRequest['user2_id']; //申请人的id
        switch ($matchUserRequest->method()) {
            //此处暂留一个接口安全隐患，如果用户直接请求接口，仍能对黑名单中用户发送邀请，暂通过其他接口保护措施规避，若有问题需要补全
            case 'POST':
                //防止恶意请求，因为无法确定当前用户和发送邀请的用户，在匹配关系表中是邀请方还是接受方，故做此写法，一般情况下不会出现这个问题
                if (!empty(UserMatchInfo::where('user1_id', $user1_id)
                    ->orwhere('user2_id', $user1_id)
                    ->orwhere('user1_id', $user2_id)
                    ->orwhere('user2_id', $user2_id)
                    ->get()
                    ->first())) {
                    $array = [
                        'message' => '该用户或邀请方已有匹配对象',
                        'status_code' => 403
                    ];
                    return $this->response->array($array);
                }

                //防止恶意请求，只允许合法用户进行匹配
                if (!User::find($user2_id)) {
                    $array = [
                        'message' => '邀请方不存在',
                        'status_code' => 403
                    ];
                    return $this->response->array($array);
                }

                //防止恶意请求，检测邀请人是否合法
                if (empty(UserAwaitMatchInfo::where('user1_id', $user2_id)
                    ->where('state', null)->get()->first()->user1_id)) {
                    $array = [
                        'message' => '对方还没有发起邀请噢',
                        'status_code' => 403
                    ];
                    return $this->response->array($array);
                }

                //防止恶意请求，检测当前用户是否在对方邀请列表里
                if (UserAwaitMatchInfo::where('user1_id', $user2_id)
                        ->where('state', null)->get()->first()->user2_id != $user1_id) {
                    $array = [
                        'message' => '你没有被对方邀请噢',
                        'status_code' => 403
                    ];
                    return $this->response->array($array);
                }

                $userMatchInfo->user1_id = $user1_id;
                $userMatchInfo->user2_id = (int)$matchUserRequest['user2_id'];
                if ($userMatchInfo->save()) {
                    UserAwaitMatchInfo::where('user2_id', $user1_id)
                        ->where('user1_id', '!=', $user2_id)
                        ->where('state', null)
                        ->update(['state' => false]);
                    UserAwaitMatchInfo::where('user2_id', $user1_id)
                        ->where('user1_id', $user2_id)
                        ->where('state', null)
                        ->update(['state' => true]);
                }
                return $this->response->item($userMatchInfo, new UserMatchInfoTransformer());
                break;
            case 'DELETE':
                $user_id = UserMatchInfo::where('user1_id', $this->user()->id)
                    ->orwhere('user2_id', $this->user()->id)
                    ->get()->first();

                //防止恶意请求，检测是否存在研友
                if (empty($user_id)) {
                        $array = [
                            'message' => '你还没有匹配研友',
                            'status_code' => 403
                        ];
                        return $this->response->array($array);
                }
                //因为不确定当前用户在匹配关系表中的字段，所以user1和user2字段的值都要重新获取
                $user1_id=$user_id->user1_id;
                $user2_id=$user_id->user2_id;

                //同样，因为不确定当前用户在待匹配关系表中的字段顺序，所以进行两次查询更改操作
                UserAwaitMatchInfo::where('user1_id', $user1_id)
                    ->where('user2_id', $user2_id)
                    ->update(['state'=>false]);
                UserAwaitMatchInfo::where('user1_id', $user2_id)
                    ->where('user2_id', $user1_id)
                    ->update(['state'=>false]);

                //删除匹配关系表中的这两名关联用户
                UserMatchInfo::where('user1_id', $this->user()->id)
                    ->orwhere('user2_id', $this->user()->id)
                    ->delete();
                return $this->response->array(['status_code'=>200]);
                break;
        }
    }

    /**
     * Notes: 发送匹配邀请,若已存在邀请对象，则返回403和邀请对象,否则返回200
     * @param AwaitMatchUserRequest $awaitMatchUserRequest
     * @param UserAwaitMatchInfo $userAwaitMatchInfo
     * @return \Dingo\Api\Http\Response
     */
    public function awaitMatchUsersStore(AwaitMatchUserRequest $awaitMatchUserRequest, UserAwaitMatchInfo $userAwaitMatchInfo)
    {
        $user1_id = $this->user()->id;

        switch ($awaitMatchUserRequest->method()) {
            //此处暂留一个接口安全隐患，如果用户直接请求接口，仍能对黑名单中用户发送邀请，暂通过其他接口保护措施规避，若有问题需要补全
            case 'POST':
                if (!empty($user2_id = UserAwaitMatchInfo::where('user1_id', $user1_id)
                    ->where('state', null)
                    ->get()
                    ->first())) {
                    $array = [
                        'message' => '用户已有待匹配对象',
                        'user2_id' => $user2_id->user2_id,
                        'status_code' => 403
                    ];
                    return $this->response->array($array);
                }

                $userAwaitMatchInfo->user1_id = $user1_id;
                $userAwaitMatchInfo->user2_id = (int)$awaitMatchUserRequest['user2_id'];
                $userAwaitMatchInfo->expired_at = date('Y-m-d H:i:s', time() + 43200); //邀请链接12个小时后过期，视为被邀请方拒绝接受邀请
                $userAwaitMatchInfo->save();
                return $this->response->item($userAwaitMatchInfo, new UserAwaitMatchInfoTransformer());
                break;
            case 'DELETE':
                $user2_id = UserAwaitMatchInfo::where('user1_id', (int)$awaitMatchUserRequest['user2_id'])//此时参数中的user2为发送邀请的用户，即待匹配表中的user1
                ->where('user2_id', $user1_id)//拒绝邀请时，此时的用户为被邀请对象，即待匹配表中的user2,通过user1和state能唯一确定被拒绝对象，但以防万一，对user2也做验证
                ->where('state', null);
                if (empty($user2_id->get()->first())) {
                    $array = [
                        'message' => '此邀请人不存在',
                        'status_code' => 403
                    ];
                    return $this->response->array($array);
                } else {
                    $user2_id->update(['state' => false]);
                    return $this->response->array(['status_code' => 200]);
                }
        }
    }

    /**
     * Notes:获取用户当前的申请列表
     * @return \Dingo\Api\Http\Response
     * 返回数组，user1_id表示当前用户，user2_id表示当前邀请的对象
     */
    public function awaitMatchUsersShow()
    {
        $userAwaitMatchInfos = UserAwaitMatchInfo::select('user1_id')
            ->where('user2_id', $this->user()->id)
            ->where('state', null)
            ->get();
        $user1_base_infos = new Collection();
        foreach ($userAwaitMatchInfos as $userAwaitMatchInfo) {
            $user1_base_infos->push(User::find($userAwaitMatchInfo->user1_id)->baseInfo()->get()->first());
        }
        return $this->response->item($user1_base_infos, new UserBaseInfoTransformer());
    }


    /**
     * Notes:
     * 辅助函数
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
