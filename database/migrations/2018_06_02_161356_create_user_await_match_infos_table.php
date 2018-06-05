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
                $table->integer('user1_id')->index();
                $table->integer('user2_id')->index();
                $table->string('share_url')->nullable();
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
