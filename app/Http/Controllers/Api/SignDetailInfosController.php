<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SignDetailInfoRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSignDetailInfo;
use App\Models\UserSignInfo;
use App\Transformers\UserSignInfoTransformer;
use Carbon\Carbon;

class SignDetailInfosController extends Controller
{
    public function store(SignDetailInfoRequest $signDetailInfoRequest, UserSignDetailInfo $signDetailInfo)
    {
        $now_time = Carbon::now();
        $today_time = Carbon::today();
        $begin_time = Carbon::today()->addHours(1);//五点开始打卡
        $end_time = Carbon::today()->addHours(8);//八点结束打卡
//        dd(array(
//            'carbon' =>Carbon::today(),
//            'today_val' => $today_time,
//            'begin_val' => $begin_time,
//            'end_val' => $end_time,
//        ));

        if ($now_time < $begin_time) {
            return $this->response->array(['message'=>'打卡还没有开始'])->setStatusCode(403);
        } elseif ($now_time > $end_time) {
            return $this->response->array(['message'=>'打卡已经结束了'])->setStatusCode(403);
        } else {
            //判断当前用户是否今天已经打卡
            if (!empty(UserSignDetailInfo::where('user_id', $this->user()->id)
                ->where('day_timestamp', $today_time)
                ->get()->first())) {
                return $this->response->array(['message'=>'今天已经打过卡了噢'])->setStatusCode(403);
            } else {
                if (!empty($match_user_id = $this->user()->matchUser())) {
                    if (empty($user2_signinfo = User::find($match_user_id)->signInfo()->get()->first())) {
                        $user2_signinfo = new UserSignInfo();
                        $user2_signinfo->user_id = $match_user_id;
                        $user2_signinfo->save();
                    }
                    if (empty($user_signinfo = $this->user()->signInfo()->get()->first())) {
                        $user_signinfo = new UserSignInfo();
                        $user_signinfo->user_id = $this->user()->id;
                        $user_signinfo->save();
                    }

                    switch (empty(UserSignDetailInfo::where('user_id', $match_user_id)
                    ->where('day_timestamp', $today_time)
                    ->get()->first())) {
                        case true:
                        //研友未打卡，用户获得一分
                            $new_user_signscore=$user_signinfo->sign_score+1;
                            $user_signday=$user_signinfo->sign_day;
                            $new_sign_day = $user_signday + 1;
                            $user_signinfo->update(['sign_day'=>$new_sign_day,'sign_score'=>$new_user_signscore]);
                            break;
                        case false:
                        //研友已打卡，用户获得1.5分，研友再获得0.5分
                            $new_user_signscore=$user_signinfo->sign_score+1.5;
                            $user_signday=$user_signinfo->sign_day;
                            $new_sign_day = $user_signday + 1;
                            $user_signinfo->update(['sign_day'=>$new_sign_day,'sign_score'=>$new_user_signscore]);
                            $new_user2_signscore = $user2_signinfo->sign_score+0.5;
                            $user2_signinfo->update(['sign_score'=>$new_user2_signscore]);
                            break;
                    }
                    $signDetailInfo->user_id=$this->user()->id;
                    $signDetailInfo->day_timestamp=$today_time;
                    $signDetailInfo->sign_timestamp=$now_time;
                    $signDetailInfo->save();
                    return $this->response->item($user_signinfo, new UserSignInfoTransformer);
                } else {
                    if (empty($user_signinfo = $this->user()->signInfo()->get()->first())) {
                        $user_signinfo = new UserSignInfo();
                        $user_signinfo->user_id = $this->user()->id;
                        $user_signinfo->save();
                    }
                    $new_user_signscore=$user_signinfo->sign_score+1;
                    $user_signday=$user_signinfo->sign_day;
                    $new_sign_day = $user_signday + 1;
                    $user_signinfo->update(['sign_day'=>$new_sign_day,'sign_score'=>$new_user_signscore]);
                    return $this->response->item($user_signinfo, new UserSignInfoTransformer);
                }
            }
        }
    }
}
