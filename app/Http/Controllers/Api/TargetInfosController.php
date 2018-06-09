<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TargetInfoRequest;
use App\Models\UserTargetInfo;
use App\Transformers\UserTargetInfoTransformer;
use App\Http\Controllers\Controller;

class TargetInfosController extends Controller
{
    public function store(TargetInfoRequest $baseInfoRequest, UserTargetInfo $userTargetInfo)
    {
        if (!empty($this->user()->targetinfo()->get()[0])) {
            return $this->response->errorForbidden("用户已设置目标信息");
        }


        $user = $this->user();
        $userTargetInfo->user_id= $user->id;
        $userTargetInfo->sex=$baseInfoRequest['sex'];
        $userTargetInfo->hometown=$baseInfoRequest['hometown'];
        $userTargetInfo->area=$baseInfoRequest['area'];
        $userTargetInfo->school_place=$baseInfoRequest['school_place'];
        $userTargetInfo->school_name=$baseInfoRequest['school_name'];
        $userTargetInfo->school_field=$baseInfoRequest['school_field'];
        $userTargetInfo->school_type=$baseInfoRequest['school_type'];
        $userTargetInfo->study_style=$baseInfoRequest['study_style'];
        $userTargetInfo->good_subject=$baseInfoRequest['good_subject'];
        $userTargetInfo->save();
        return $this->response->item($userTargetInfo, new UserTargetInfoTransformer());
    }
    public function show()
    {
        $userTargetInfo = $this->user()->targetinfo()->get();
        return $this->response->item($userTargetInfo, new UserTargetInfoTransformer());
    }
}
