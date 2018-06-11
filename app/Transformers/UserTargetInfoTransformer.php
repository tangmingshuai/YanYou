<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/9
 * Time: 1:21
 */

namespace App\Transformers;
use App\Models\UserTargetInfo;
use League\Fractal\TransformerAbstract;

class UserTargetInfoTransformer extends TransformerAbstract
{
    public function transform(UserTargetInfo $userTargetInfo)
    {
        return [
            'data'=> $userTargetInfo->toArray(),
            'status'=>'200'
        ];
    }
}
