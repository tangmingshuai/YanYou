<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SignInfoRequest;
use App\Models\UserSignDetailInfo;
use App\Models\UserSignInfo;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class SignInfosController extends Controller
{
    use Helpers;
    public function show()
    {
        if (empty($user_signinfo = $this->user()->signInfo()->get()->first())) {
            return $this->response->array(['message'=>'用户还没有开始打卡'])->setStatusCode(404);
        } else {
            $is_sign_today = UserSignDetailInfo::where('user_id', $this->user()->id)
                ->where('day_timestamp', Carbon::today())
                ->get()->first();
            $json_array=[
                'user_id' => $user_signinfo->user_id,
                'sign_day' =>$user_signinfo->sign_day,
                'sign_score' =>$user_signinfo->sign_score,
                'is_sign_today' => !empty($is_sign_today)
            ];

            $sign_detail_infos = UserSignInfo::orderBy('sign_day', 'desc')
                ->join('user_weixin_infos', 'user_sign_infos.user_id', '=', 'user_weixin_infos.user_id')
                ->get()->toArray();
            $signInfo = array_filter($sign_detail_infos, function($k) {
                return $k['user_id'] == 54;
            });
            $json_array['sign_rank'] = array_keys($signInfo)[0];

            return $this->response->array($json_array);
        }
    }
}
