<?php
/**
 * Created by PhpStorm.
 * User: Think
 * Date: 2018/6/2
 * Time: 17:49
 */
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Common extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'CommonUtil';
    }

}