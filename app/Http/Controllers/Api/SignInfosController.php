<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SignInfoRequest;
use App\Models\UserSignInfo;
use App\Http\Controllers\Controller;
use App\Transformers\UserSignInfoTransformer;

class SignInfosController extends Controller
{
    public function store(SignInfoRequest $signInfoRequest, UserSignInfo $signInfo)
    {
        $user_signinfo = $this->user()->get;
        $user_signday=$user_signinfo->signday;
        $new_sign_day = $user_signday + 1;
        $user_signinfo::update(['sign_day'=>$new_sign_day]);
    }

    public function show()
    {
        $user_signinfo = $this->user()->signInfo()->get()->first();
        return $this->response->item($user_signinfo, new UserSignInfoTransformer);
    }

    public function update()
    {
        $user_id = $this->user()->signInfo()->get;
    }
}
