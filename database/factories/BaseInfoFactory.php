<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserBaseInfo::class, function (Faker $faker) {
    $date_time = $faker->date . ' ' . $faker->time;

    return [
        'user_id' => function () {
            return factory(\App\Models\User::class)->create()->id;
        },
        'name' => $faker ->name(),
        'phone' => $faker ->phoneNumber,
        'sex' => $faker ->randomElement(['男', '女']),
        'hometown' => $faker ->randomElement(['武汉', '北京','上海','广州','重庆']),
        'area' => $faker->randomElement(['北区', '南区']),
        'school_place' => $faker ->randomElement(['武汉', '北京','上海','广州','重庆']),
        'school_name' => $faker ->randomElement(['北大', '清华','上交','复旦','人大']),
        'school_field' => $faker ->randomElement(['计科', '生物','化学','物理','文传']),
        'school_type' =>$faker->randomElement(['学硕', '专硕']),
        'study_style' =>$faker->randomElement(['单独', '团体']),
        'good_subject' => $faker ->randomElement(['数学', '英语','化学','物理','生物']),
        'created_at' => $date_time,
        'updated_at' => $date_time,

    ];
});
