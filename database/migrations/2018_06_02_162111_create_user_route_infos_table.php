<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRouteInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_route_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->enum('router_id', ['0', '1', '2'])->index()->comment('0:主页,1:匹配页,2:打卡页');
            $table->enum('match_state', ['0', '1', '2'])->index()->comment('0:主页,1:匹配页,2:打卡页');
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
        Schema::dropIfExists('user_route_infos');
    }
}
