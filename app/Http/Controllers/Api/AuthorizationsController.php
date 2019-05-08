<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\WeappAuthorizationRequest;
use App\Models\UserWeixinInfo;
use Auth;
use App\Models\User;
use App\Transformers\DataTransformer;
use App\Http\Requests\Api\AuthorizationRequest;

class AuthorizationsController extends Controller
{
    public function weappStore(WeappAuthorizationRequest $request)
    {
        $code = $request->code;

        // 根据 code 获取微信 openid 和 session_key
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code 不正确');
        }

        // 找到 openid 对应的用户
        $user = User::where('weapp_openid', $data['openid'])->first();

        $attributes['weixin_session_key'] = $data['session_key'];

        // 未找到对应用户则创建新用户
        if (!$user) {
            if (empty($request->nickname)||empty($request->avatar)) {
                return $this->response->errorUnauthorized('缺少用户微信信息');
            }
            // 获取对应的用户
            $user = new User();
            $attributes['weapp_openid'] = $data['openid'];
            $user = $user->create($attributes);
            $user_weixin_info = new UserWeixinInfo();
            $user_weixin_info->user_id = $user->id;
            $user_weixin_info->nickname = $request->nickname;
            $user_weixin_info->avatar = $request->avatar;
            $user_weixin_info->save();
        }

        // 更新用户数据
        $user->update($attributes);

        // 为对应用户创建 JWT
        $token = Auth::guard('api')->fromUser($user);

        $json_array=[
            'user_id'            => $user->id,
            'access_token'       => $token,
            'token_type'         => 'Bearer',
            'expires_in'         => Auth::guard('api')->factory()->getTTL() * 60,
            'weapp_openid'       => $data['openid'],
            'weixin_session_key' => $data['session_key']
        ];
        return $this->response->array($json_array)->setStatusCode(201);
    }

    /**
     * Notes:
     * @param AuthorizationRequest $request
     */
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;

        //account和password对应数据库字段
        $credentials['account'] = $username;
        $credentials['password'] = $request->password;


        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->response->errorUnauthorized(trans('auth.failed'));
        }
        return $this->respondWithToken($token)->setStatusCode(201);
    }


    public function update()
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    public function destroy()
    {
        Auth::guard('api')->logout();
        return $this->response->noContent();
    }

    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
