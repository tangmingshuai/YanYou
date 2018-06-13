<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/14
 * Time: 3:25
 */

namespace App\Transformers;

use App\Models\UserSignDetailInfo;
use League\Fractal\TransformerAbstract;

class UserSignDetailInfoTransformer extends TransformerAbstract
{

    public function transform(UserSignDetailInfo $userSignDetailInfo)
    {
        return [
            $userSignDetailInfo->toArray(),
        ];
    }
}