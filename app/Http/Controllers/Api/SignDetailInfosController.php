<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\SignDetailInfoRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Dingo\Api\Routing\Helpers;
use App\Models\UserBaseInfo;
use App\Models\UserSignDetailInfo;
use App\Models\UserSignInfo;
use App\Transformers\UserSignDetailInfoTransformer;
use App\Transformers\UserSignInfoTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SignDetailInfosController extends Controller
{
    use Helpers;
    public function store(UserSignDetailInfo $signDetailInfo)
    {
        $now_time = date("Y-m-d H:i:s", time());
//        $now_time = Carbon::now();//存在小bug，会在秒后带小数点，偶尔时间也全错，先不用这个了
        $today_time = Carbon::today();
        $begin_time = Carbon::today()->addHours(6);//五点开始打卡
        $end_time = Carbon::today()->addHours(20)->addMinutes(30);//八点结束打卡
//        dd(array(
//            'now_val' => $now_time,
//            'carbon_today' =>Carbon::today(),
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

                    //研友已打卡，则当天排名一定不为空
                    $now_rank = UserSignDetailInfo::select('sign_rank')
                        ->where('day_timestamp', Carbon::today())
                        ->max('sign_rank');
                    $signDetailInfo->sign_rank= $now_rank + 1;

                    //判断是否为该专业第一个打卡，计算专业排名
                    $user_major = $this->user()->baseInfo()->get()->first()->school_field;
                    $major_users= array_flatten(UserBaseInfo::select('user_id')
                        ->where('school_field', $user_major)
                        ->where('user_id', '!=', $this->user()->id)
                        ->get()->toArray());

                    $now_major_rank=UserSignDetailInfo::select('sign_major_rank')
                        ->whereIn('user_id', $major_users)
                        ->where('day_timestamp', Carbon::today())
                        ->max('sign_major_rank');
                    empty($now_major_rank)?$signDetailInfo->sign_major_rank= 1:
                        $signDetailInfo->sign_major_rank= $now_major_rank + 1 ;

                    $signDetailInfo->user_id=$this->user()->id;
                    $signDetailInfo->day_timestamp=$today_time;
                    $signDetailInfo->sign_timestamp=$now_time;
                    $signDetailInfo->save();
                    $json_array = [
                        'user_id' => $signDetailInfo->user_id,
                        'user_major' =>$user_major,
                        'sign_rank' => $signDetailInfo->sign_rank,
                        'sign_major_rank' => $signDetailInfo->sign_major_rank,
                        'sign_timestamp' =>$signDetailInfo->sign_timestamp,
                        'sign_day' => $user_signinfo->sign_day,
                        'sign_score' => $user_signinfo->sign_score,
                    ];
                    return $this->response->array($json_array);
                } else { //单人打卡逻辑处理
                    //用户第一次打卡，初始化sign_info表信息
                    if (empty($user_signinfo = $this->user()->signInfo()->get()->first())) {
                        $user_signinfo = new UserSignInfo();
                        $user_signinfo->user_id = $this->user()->id;
                        $user_signinfo->save();
                    }

                    //判断是否为所有用户当天第一个打卡，计算排名
                    $now_rank = UserSignDetailInfo::select('sign_rank')
                        ->where('day_timestamp', Carbon::today())
                        ->max('sign_rank');
                    empty($now_rank)?$signDetailInfo->sign_rank= 1:$signDetailInfo->sign_rank= $now_rank + 1 ;

                    //判断是否为该专业第一个打卡，计算专业排名
                    $user = $this->user()->baseInfo()->get()->first();
                    $signDetailInfo->sign_major_rank = 0;
                    if($user){
                        $user_major = $user->school_field;
                        $major_users= array_flatten(UserBaseInfo::select('user_id')
                            ->where('school_field', $user_major)
                            ->where('user_id', '!=', $this->user()->id)
                            ->get()->toArray());

                        $now_major_rank=UserSignDetailInfo::select('sign_major_rank')
                            ->whereIn('user_id', $major_users)
                            ->where('day_timestamp', Carbon::today())
                            ->max('sign_major_rank');
                        empty($now_major_rank)?$signDetailInfo->sign_major_rank= 1:
                            $signDetailInfo->sign_major_rank = $now_major_rank + 1 ;
                    }

                    $signDetailInfo->user_id=$this->user()->id;
                    $signDetailInfo->day_timestamp=$today_time;
                    $signDetailInfo->sign_timestamp=$now_time;
                    $signDetailInfo->save();
                    $new_user_signscore=$user_signinfo->sign_score+1;
                    $user_signday=$user_signinfo->sign_day;
                    $new_sign_day = $user_signday + 1;
                    $user_signinfo->update(['sign_day'=>$new_sign_day,'sign_score'=>$new_user_signscore]);
                    $json_array = [
                        'user_id' => $signDetailInfo->user_id,
                        'user_major' =>$user_major,
                        'sign_rank' => $signDetailInfo->sign_rank,
                        'sign_major_rank' => $signDetailInfo->sign_major_rank,
                        'sign_timestamp' =>$signDetailInfo->sign_timestamp,
                        'sign_day' => $user_signinfo->sign_day,
                        'sign_score' => $user_signinfo->sign_score,
                    ];
                    return $this->response->array($json_array);
                }
            }
        }
    }

    /**
     * Notes:可传入一个时间戳，自动获取时间戳所在日期的打卡详情，无参数时返回当天打卡详情
     * 没有打卡信息时，返回404
     * @param SignDetailInfoRequest $signDetailInfoRequest
     * @return mixed
     */
    public function show(SignDetailInfoRequest $signDetailInfoRequest)
    {
        //无参数时，默认查询当天打卡详情信息
        $query_day = empty($signDetailInfoRequest['day_timestamp'])?
            Carbon::today():
            date("Y-m-d", $signDetailInfoRequest['day_timestamp']);

        $sign_detail_infos = UserSignDetailInfo::select('user_id', 'sign_rank', 'sign_major_rank', 'sign_timestamp')
            ->where('day_timestamp', $query_day)
            ->where('user_id', $this->user()->id)
            ->orderBy('sign_rank', 'asc')
            ->get()->first();

        if (empty($sign_detail_infos)) {
            return $this->response->array(['message'=>'这一天没有打卡信息'])->setStatusCode(404);
        }

        $userBaseInfo = $this->user()->baseInfo()->get()->first();
        Log::notice($userBaseInfo);
        $json_array = [
            'user_id' => $sign_detail_infos->user_id,
            'sign_rank' => $sign_detail_infos->sign_rank,
            'sign_major_rank' => $sign_detail_infos->sign_major_rank,
            'sign_timestamp' =>$sign_detail_infos->sign_timestamp,
        ];
        if (!empty($userBaseInfo->school_field)){
            $json_array['user_major'] = $userBaseInfo->school_field;
        }
        return $this->response->array($json_array);
    }

    public function showRankAll()
    {
        $sign_detail_infos = UserSignDetailInfo::where('day_timestamp', Carbon::today())
            ->orderBy('sign_rank', 'asc')
            ->join('user_weixin_infos', 'user_sign_detail_infos.user_id', '=', 'user_weixin_infos.user_id')
            ->get()->toArray();
        if (empty($sign_detail_infos)) {
            return $this->response->array(['message'=>'今天还无人打卡'])->setStatusCode(404);
        } else {
            return $this->response->array($sign_detail_infos);
        }
    }
    public function showScoreAll()
    {
        $sign_detail_infos = UserSignInfo::orderBy('sign_score', 'desc')
            ->join('user_weixin_infos', 'user_sign_infos.user_id', '=', 'user_weixin_infos.user_id')
            ->take(10)
            ->get()->toArray();

        if (empty($sign_detail_infos)) {
            return $this->response->array(['message'=>'还没有人打卡'])->setStatusCode(404);
        } else {
            return $this->response->array($sign_detail_infos);
        }
    }
    public function showDayAll()
    {
        $sign_detail_infos = UserSignInfo::orderBy('sign_day', 'desc')
            ->join('user_weixin_infos', 'user_sign_infos.user_id', '=', 'user_weixin_infos.user_id')
            ->take(10)
            ->get()->toArray();

        if (empty($sign_detail_infos)) {
            return $this->response->array(['message'=>'还没有人打卡'])->setStatusCode(404);
        } else {
            return $this->response->array($sign_detail_infos);
        }
    }
}
