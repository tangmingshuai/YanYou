<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Logic\AccountInfoLogic;

class AccountInfoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('AccountInfoLogic', function () {
            return new AccountInfoLogic();
        });
    }
}
