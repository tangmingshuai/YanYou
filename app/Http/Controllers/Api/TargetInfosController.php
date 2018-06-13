<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TargetInfoRequest;
use App\Models\UserTargetInfo;
use App\Transformers\UserTargetInfoTransformer;
use App\Http\Controllers\Controller;

class TargetInfosController extends Controller
{
    public function store(TargetInfoRequest $targetInfoRequest, UserTargetInfo $userTargetInfo)
    {
        $user = $this->user();

        if (!empty($user->targetinfo()->get()->first())) {
            $this->response->errorForbidden("用户已设置目标信息");
        }

        $userTargetInfo->user_id= $user->id;
        $userTargetInfo->sex=$targetInfoRequest['sex'];
        $userTargetInfo->hometown=$targetInfoRequest['hometown'];
        $userTargetInfo->area=$targetInfoRequest['area'];
        $userTargetInfo->school_place=$targetInfoRequest['school_place'];
        $userTargetInfo->school_name=$targetInfoRequest['school_name'];
        $userTargetInfo->school_field=$targetInfoRequest['school_field'];
        $userTargetInfo->school_type=$targetInfoRequest['school_type'];
        $userTargetInfo->study_style=$targetInfoRequest['study_style'];
        $userTargetInfo->good_subject=$targetInfoRequest['good_subject'];
        $userTargetInfo->save();
        return $this->response->item($userTargetInfo, new UserTargetInfoTransformer())->setStatusCode(201);
    }
    public function show()
    {
        $userTargetInfo = $this->user()->targetinfo()->get();
        return $this->response->item($userTargetInfo, new UserTargetInfoTransformer());
    }
}
