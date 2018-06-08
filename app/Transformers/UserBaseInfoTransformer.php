<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/8
 * Time: 11:27
 */
namespace App\Transformers;

use App\Models\UserBaseInfo;
use League\Fractal\TransformerAbstract;

class UserBaseInfoTransformer extends TransformerAbstract
{
    public function transform(UserBaseInfo $userBaseInfo)
    {
//        return [
//            'id' => $user->id,
//            'name' => $user->name,
//            'email' => $user->email,
//            'avatar' => $user->avatar,
//            'introduction' => $user->introduction,
//            'bound_phone' => $user->phone ? true : false,
//            'bound_wechat' => ($user->weixin_unionid || $user->weixin_openid) ? true : false,
//            'last_actived_at' => $user->last_actived_at->toDateTimeString(),
//            'created_at' => $user->created_at->toDateTimeString(),
//            'updated_at' => $user->updated_at->toDateTimeString(),
//        ]
        return [
            'data'=> $userBaseInfo->toArray(),
            'status'=>'200'
        ];
    }
}
