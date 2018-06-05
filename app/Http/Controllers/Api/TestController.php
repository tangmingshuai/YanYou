<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    //
    public function store(Request $request)
    {
        $result=app('AccountInfoLogic')->judgeAccount($request->toArray());
        return $this->response->array($result);
    }
}
