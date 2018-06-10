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
            $table->integer('user_id')->unique()->index();
            $table->enum('sex', ['男', '女','不介意']);
            $table->string('hometown');
            $table->enum('area', ['北区', '南区','不介意']);
            $table->string('school_place');
            $table->string('school_name');
            $table->string('school_field');
            $table->enum('school_type', ['学硕', '专硕','不介意']);
            $table->enum('study_style', ['单独', '团体']);
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
