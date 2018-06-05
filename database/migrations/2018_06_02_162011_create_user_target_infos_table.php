<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTargetInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_target_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->enum('sex', ['male', 'female']);
            $table->string('hometown');
            $table->enum('area', ['north', 'south']);
            $table->string('school_place');
            $table->string('school_name');
            $table->string('school_field');
            $table->enum('school_type', ['xueshuo', 'zhuanshuo']);
            $table->enum('study_style', ['single', 'gruop']);
            $table->string('good_subject');
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
        Schema::dropIfExists('user_target_infos');
    }
}
