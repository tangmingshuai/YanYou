<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\BaseInfoRequest;
use App\Models\User;
use App\Models\UserBaseInfo;
use App\Models\UserSignDetailInfo;
use App\Models\UserSignInfo;
use App\Transformers\UserBaseInfoTransformer;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;

class BaseInfosController extends Controller
{
    //
    use Helpers;
    public function store(BaseInfoRequest $baseInfoRequest, UserBaseInfo $userBaseInfo)
    {
        $user = $this->user();

        if (!empty($user->baseinfo()->get()->first())) {
            $this->response->errorForbidden("用户已设置个人信息");
        }

        $userBaseInfo->user_id= $user->id;
        $userBaseInfo->name=$baseInfoRequest['name'];
        $userBaseInfo->phone=$baseInfoRequest['phone'];
        $userBaseInfo->sex=$baseInfoRequest['sex'];
        $userBaseInfo->hometown=$baseInfoRequest['hometown'];
        $userBaseInfo->area=$baseInfoRequest['area'];
        $userBaseInfo->school_place=$baseInfoRequest['school_place'];
        $userBaseInfo->school_name=$baseInfoRequest['school_name'];
        $userBaseInfo->school_field=$baseInfoRequest['school_field'];
        $userBaseInfo->school_type=$baseInfoRequest['school_type'];
        $userBaseInfo->study_style=$baseInfoRequest['study_style'];
        $userBaseInfo->good_subject=$baseInfoRequest['good_subject'];
        $userBaseInfo->save();
        return $this->response->item($userBaseInfo, new UserBaseInfoTransformer)->setStatusCode(201);
    }
    public function show()
    {
        $userInfo = array_merge($this->user()->baseinfo()->get()->toArray()[0], $this->user()->weixininfo()->get()->toArray()[0]);
        $matchUserId = $this->user()->matchUser();
        $userInfo['match_user_id'] = $matchUserId ? : 0;
        $userInfo['is_match_user_sign_today'] = false;
        if ($matchUserId){
            $userInfo['is_match_user_sign_today'] = UserSignDetailInfo::where('user_id', $matchUserId)
                ->where('day_timestamp', Carbon::today())
                ->get()->first();
        }

        return $this->response->array($userInfo, new UserBaseInfoTransformer());
    }

    public function matchShow()
    {
        $matchUserId = $this->user()->matchUser();
        $user = User::find($matchUserId);
        $userInfo['match_user_id'] = $matchUserId ? : 0;
        $userInfo['is_match_user_sign_today'] = false;
        $userInfo['match_user_info'] = $user ? array_merge($user->baseinfo()->get()->toArray()[0], $user->weixininfo()->get()->toArray()[0]) : [];
        if ($userInfo['match_user_id']){
            $userInfo['is_match_user_sign_today'] = UserSignDetailInfo::where('user_id', $matchUserId)
                ->where('day_timestamp', Carbon::today())
                ->get()->first();
        }
        return $this->response->array($userInfo, new UserBaseInfoTransformer());
    }
}
