<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/11
 * Time: 16:21
 */

namespace App\Transformers;
use App\Models\UserAwaitMatchInfo;
use League\Fractal\TransformerAbstract;


class UserAwaitMatchInfoTransformer extends TransformerAbstract
{

    public function transform(UserAwaitMatchInfo $userAwaitMatchInfo)
    {
        return [
            $userAwaitMatchInfo->toArray(),
        ];
    }
}