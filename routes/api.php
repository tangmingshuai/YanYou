<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['serializer:array', 'bindings', 'change-locale']
], function ($api) {
    //调试
    $api->post('test', 'TestController@store');
    // 登录
    $api->post('authorizations', 'AuthorizationsController@store')
        ->name('api.authorizations.store');
    // 小程序登录
    $api->post('weapp/authorizations', 'AuthorizationsController@weappStore')
        ->name('api.weapp.authorizations.store');
    // 小程序注册
    $api->post('weapp/users', 'UsersController@weappStore')
        ->name('api.weapp.users.store');

    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' => config('api.rate_limits.sign.expires'),
    ], function ($api) {
        // 用户注册
        $api->post('users', 'UsersController@store')
            ->name('api.users.store');
        // 登录
        $api->post('authorizations', 'AuthorizationsController@store')
            ->name('api.authorizations.store');
        // 刷新token
        $api->put('authorizations/current', 'AuthorizationsController@update')
            ->name('api.authorizations.update');
        // 删除token
        $api->delete('authorizations/current', 'AuthorizationsController@destroy')
            ->name('api.authorizations.destroy');
    });

    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.access.limit'),
        'expires' => config('api.rate_limits.access.expires'),
    ], function ($api) {
        // 游客可以访问的接口

        // 获取当日所有用户打卡排行
        $api->get('user/sign/ranks', 'SignDetailInfosController@showRankAll')
            ->name('api.user.sign.rank.showall');
        // 获取所有用户打卡积分排行
        $api->get('user/sign/score/ranks', 'SignDetailInfosController@showScoreAll')
            ->name('api.user.sign.score.showall');
        // 获取所有用户打卡天数排行
        $api->get('user/sign/day/ranks', 'SignDetailInfosController@showDayAll')
            ->name('api.user.sign.day.showall');

        // 需要 token 验证的接口
        $api->group(['middleware' => 'api.auth'], function ($api) {
            // 获取当前用户是否为验证学生
            $api->get('user', 'UsersController@isStudent')
                ->name('api.user.isStudent');
            // 填写个人信息
            $api->post('user/baseinfo', 'BaseInfosController@store')
                ->name('api.baseinfo.store');
            // 获取个人信息
            $api->get('user/baseinfo', 'BaseInfosController@show')
                ->name('api.baseinfo.show');
            // 填写研友目标信息
            $api->post('user/targetinfo', 'TargetInfosController@store')
                ->name('api.targetinfo.store');
            // 获取研友目标信息
            $api->get('user/targetinfo', 'TargetInfosController@show')
                ->name('api.targetinfo.show');
            // 获取匹配的研友信息
            $api->get('user/match', 'UsersController@matchUsersShow')
                ->name('api.user.get.match.users');
            // 接受匹配邀请，完成匹配
            $api->post('user/match', 'UsersController@matchUsersStore')
                ->name('api.user.match.user.store');
            // 解除研友关系
            $api->delete('user/match', 'UsersController@matchUsersStore')
                ->name('api.user.match.user.delete');

            // 拒绝匹配邀请
            $api->delete('user/awaitmatch', 'UsersController@awaitMatchUsersStore')
                ->name('api.user.await.match.user.delete');
            // 发送研友匹配邀请
            $api->post('user/awaitmatch', 'UsersController@awaitMatchUsersStore')
                ->name('api.user.await.match.user.store');
            // 获取研友匹配邀请信息
            $api->get('user/awaitmatch', 'UsersController@awaitMatchUsersShow')
                ->name('api.user.await.match.user,show');

            // 获取用户打卡信息
            $api->get('user/sign', 'SignInfosController@show')
                ->name('api.user.sign.show');
            // 创建和更新用户打卡信息
            $api->patch('user/sign', 'SignDetailInfosController@store')
                ->name('api.user.sign.store');
            // 获取用户打卡排行
            $api->get('user/sign/rank', 'SignDetailInfosController@show')
                ->name('api.user.sign.rank.show');

        });
    });
});

$api->version('v2', function ($api) {
    $api->get('version', function () {
        return response('this is version v2');
    });
});
