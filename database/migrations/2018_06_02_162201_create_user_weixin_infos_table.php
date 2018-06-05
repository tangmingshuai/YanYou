<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWeixinInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_weixin_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->string('nickname')->nullable();
            $table->string('avatar')->nullable();
            $table->string('introduction')->nullable();
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
        Schema::dropIfExists('user_weixin_infos');
    }
}