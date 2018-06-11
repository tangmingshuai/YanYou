<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserMatchInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_match_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user1_id')->unique()->index()->comment('接受邀请用户的id');
            $table->integer('user2_id')->unique()->index()->comment('发送邀请用户的id');
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
        Schema::dropIfExists('user_match_infos');
    }
}
