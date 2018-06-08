<?php

use Faker\Generator as Faker;
use Carbon\Carbon;

$factory->define(App\Models\User::class, function (Faker $faker) {
    static $password;
    $now = Carbon::now()->toDateTimeString();

    return [
        'account' => $faker->buildingNumber,
        'password' => bcrypt('123456'),
        'created_at' => $now,
        'updated_at' => $now,
    ];
});