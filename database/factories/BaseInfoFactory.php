<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserBaseInfo::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;

    return [
        'name' => $faker ->name(),
        'phone' => $faker ->phoneNumber,
        'sex' => $faker ->randomElement(['男', '女']),
        'hometown' => '武汉',
        'area' => $faker->randomElement(['北区', '南区']),
        'school_place' => '北京',
        'school_name' => '北大',
        'school_field' => '计科',
        'hometown' => '上海',
        'school_type' =>$faker->randomElement(['学硕', '专硕']),
        'study_style' =>$faker->randomElement(['单独', '团体']),
        'good_subject' => '数学',
        'created_at' => $date_time,
        'updated_at' => $date_time,

    ];
});
