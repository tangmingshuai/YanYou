<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('account')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('weixin_openid')->unique()->nullable();
            $table->string('weixin_unionid')->unique()->nullable();
            $table->string('weapp_openid')->unique()->nullable();
            $table->string('weixin_session_key')->nullable();
            $table->rememberToken()->nullable();
            $table->integer('notification_count')->unsigned()->default(0);
            $table->timestamp('last_actived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
