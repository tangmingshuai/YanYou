<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\UserBaseInfo;
use App\Models\UserSignInfo;
use Dingo\Api\Routing\Helpers;
use App\Models\UserSignDetailInfo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BaseInfoRequest;
use App\Transformers\UserBaseInfoTransformer;
use Illuminate\Support\Facades\Log;

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

    public function update(BaseInfoRequest $baseInfoRequest, UserBaseInfo $userBaseInfo)
    {
        $user = $this->user();

        $update['name']= $baseInfoRequest['name'] ? $baseInfoRequest['name'] : ' ';
        $update['phone']=$baseInfoRequest['phone'] ? $baseInfoRequest['phone'] : ' ';
        $update['sex']=$baseInfoRequest['sex'] ? $baseInfoRequest['sex'] : '男';
        $update['hometown']=$baseInfoRequest['hometown'] ? $baseInfoRequest['hometown'] : ' ';
        $update['area']=$baseInfoRequest['area'] ? $baseInfoRequest['area'] : '北区';
        $update['school_place']=$baseInfoRequest['school_place'] ? $baseInfoRequest['school_place'] : ' ';
        $update['school_name']=$baseInfoRequest['school_name'] ? $baseInfoRequest['school_name'] : ' ';
        $update['school_field']=$baseInfoRequest['school_field'] ? $baseInfoRequest['school_field'] : ' ';
        $update['school_type']=$baseInfoRequest['school_type'] ? $baseInfoRequest['school_type'] : '学硕';
        $update['study_style']=$baseInfoRequest['study_style'] ? $baseInfoRequest['study_style'] : '单独';
        $update['good_subject']=$baseInfoRequest['good_subject'] ? $baseInfoRequest['good_subject'] : ' ';
        if($userBaseInfo->where('user_id', $user->id)->get()->first())
        {
            $userBaseInfo->where('user_id', $user->id)->update($update);
        }else{
            $userBaseInfo->user_id= $user->id;
            $userBaseInfo->name=$update['name'];
            $userBaseInfo->phone=$update['phone'];
            $userBaseInfo->sex=$update['sex'];
            $userBaseInfo->hometown=$update['hometown'];
            $userBaseInfo->area=$update['area'];
            $userBaseInfo->school_place=$update['school_place'];
            $userBaseInfo->school_name=$update['school_name'];
            $userBaseInfo->school_field=$update['school_field'];
            $userBaseInfo->school_type=$update['school_type'];
            $userBaseInfo->study_style=$update['study_style'];
            $userBaseInfo->good_subject=$update['good_subject'];
            $userBaseInfo->save();
        }

        return $this->response->item($userBaseInfo, new UserBaseInfoTransformer)->setStatusCode(201);
    }

    public function show()
    {
        $userWeiXinInfo = $this->user()->weixininfo()->get()->toArray() ? : [];
        $userInfo = $this->user()->baseinfo()->get()->toArray();
        if (empty($userInfo)){
            return $this->response->error('用户未设置基本信息', 404);
        }
        $userInfo = array_merge($userInfo[0], $userWeiXinInfo);
        $matchUserId = $this->user()->matchUser();
        $userInfo['match_user_id'] = $matchUserId ? : 0;
        $userInfo['is_match_user_sign_today'] = false;
        if ($matchUserId){
            $userInfo['is_match_user_sign_today'] = UserSignDetailInfo::where('user_id', $matchUserId)
                ->where('day_timestamp', Carbon::today())
                ->get()->first();
        }

        $sign_detail_infos = UserSignInfo::orderBy('sign_day', 'desc')
            ->join('user_weixin_infos', 'user_sign_infos.user_id', '=', 'user_weixin_infos.user_id')
            ->get()->toArray();
        $signInfo = array_filter($sign_detail_infos, function($k) {
            return $k['user_id'] == 54;
        });

        $userInfo['sign_rank'] = array_keys($signInfo)[0];
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
