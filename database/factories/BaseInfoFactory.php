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
        'hometown' => $faker ->randomElement(['武汉', '北京','上海','广州','重庆','西安','山东','厦门','南京','天津']),
        'area' => $faker->randomElement(['北区', '南区']),
        'school_place' => $faker ->randomElement(['武汉', '北京','上海','广州','重庆','西安','山东','厦门','南京','天津']),
        'school_name' => $faker ->randomElement(['北大', '清华','上交','复旦','人大','华科','武大','厦大','湖大','上大',
            '西交','电子科技大','浙江大学','南京大学','吉林大学','四川大学','天津大学','山东大学','厦门大学','东南大学','同济大学',
            '不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定',
            '不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定',]), //模拟有50%的人不确定院校
        'school_field' => $faker ->randomElement(['计科', '生物','化学','物理','文传','数学','地理','英语','日语','法语',
            '不确定','不确定','不确定']),//模拟有20%的人不确定专业
        'school_type' =>$faker->randomElement(['学硕', '专硕','不确定','不确定','不确定','不确定','不确定','不确定','不确定','不确定']),//模拟有80%的人不确定考研类型
        'study_style' =>$faker->randomElement(['单独', '团体']),
        'good_subject' => $faker ->randomElement(['数学', '英语','化学','物理','生物']),
        'created_at' => $date_time,
        'updated_at' => $date_time,

    ];
});
