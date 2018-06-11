<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/8
 * Time: 11:27
 */
namespace App\Transformers;

use App\Models\UserBaseInfo;
use League\Fractal\TransformerAbstract;

class UserBaseInfoTransformer extends TransformerAbstract
{
    public function transform(UserBaseInfo $userBaseInfo)
    {
        return [
            'data'=> $userBaseInfo->toArray(),
            'status'=>'200'
        ];
    }
}
