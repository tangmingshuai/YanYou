<?php

namespace App\Providers;

use App\Models\User;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class TestUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
                array_key_exists('password', $credentials))) {
            return;
        }
        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();

        //查找数据库中是否存在该用户名
        foreach ($credentials as $key => $value) {
            if (!Str::contains($key, 'password')) {
                $query->orWhere($key, $value);
            }
        }


        //如果不存在，验证是否为有效学生，若是则添加进数据库
        if (!$query->first()) {
            //学校认证平台post字段为username，因此需要将account重新构造数组
            $usr_info = ['username' => $credentials['account'], 'password' => $credentials['password']];
            $result = app('AccountInfoLogic')->judgeAccount($usr_info);
            if ($result['status'] == 200) {
                $user = User::create([
                    'account' => $credentials['account']
                ]);
                $user->password = bcrypt($credentials['password']);
                $user->save();
                return $user;
            }

        }
        return $query->first();

    }

    public function validateCredentials(UserContract $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }
}
