<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/11
 * Time: 22:55
 */

namespace App\Transformers;

use App\Models\UserMatchInfo;
use League\Fractal\TransformerAbstract;

class UserMatchInfoTransformer extends TransformerAbstract
{
    public function transform(UserMatchInfo $userMatchInfo)
    {
        return [
            $userMatchInfo->toArray(),
        ];
    }
}