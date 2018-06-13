<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SignInfoRequest;
use App\Models\UserSignInfo;
use App\Http\Controllers\Controller;
use App\Transformers\UserSignInfoTransformer;

class SignInfosController extends Controller
{

    public function show()
    {
        if (empty($user_signinfo = $this->user()->signInfo()->get()->first())) {
            return $this->response->array(['message'=>'用户还没有开始打卡'])->setStatusCode(404);
        } else {
            return $this->response->item($user_signinfo, new UserSignInfoTransformer);
        }
    }
}
