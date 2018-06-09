<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\BaseInfoRequest;
use App\Models\UserBaseInfo;
use App\Transformers\UserBaseInfoTransformer;
use App\Http\Controllers\Controller;

class BaseInfosController extends Controller
{
    //
    public function store(BaseInfoRequest $baseInfoRequest, UserBaseInfo $userBaseInfo)
    {
        if (!empty($this->user()->baseinfo()->get()[0])) {
            return $this->response->errorForbidden("用户已设置个人信息");
        }

        $user = $this->user();
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
        return $this->response->item($userBaseInfo, new UserBaseInfoTransformer);
    }
    public function show()
    {
        $userBaseInfo = $this->user()->baseinfo()->get();
        return $this->response->item($userBaseInfo, new UserBaseInfoTransformer());
    }
}
