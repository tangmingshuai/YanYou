<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAwaitMatchInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_await_match_infos', function (Blueprint $table) {
            //
            $table->increments('id');
            $table->integer('user1_id')->index()->comment('发送邀请用户的id');
            $table->integer('user2_id')->index()->comment('被邀请用户的id');
            $table->string('share_url')->nullable();
            $table->boolean('state')->nullable();
            $table->timestamp('expired_at')->nullable();
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
        Schema::dropIfExists('user_await_match_infos');
    }
}
