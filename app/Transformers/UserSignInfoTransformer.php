<?php
/**
 * Created by PhpStorm.
 * User: YiWan
 * Date: 2018/6/12
 * Time: 15:47
 */

namespace App\Transformers;

use App\Models\UserSignInfo;
use League\Fractal\TransformerAbstract;

class UserSignInfoTransformer extends TransformerAbstract
{
    public function transform(UserSignInfo $userSignInfo)
    {
        return [
            'data' => $userSignInfo->toArray(),
            'status' => '200'
        ];
    }
}
