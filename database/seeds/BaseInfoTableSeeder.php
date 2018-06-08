<?php

use App\Models\BaseInfo;
use Illuminate\Database\Seeder;

class BaseInfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_ids = ['1'];
        $faker=app(Faker\Generator::class);
        $base_info=factory(\App\Models\UserBaseInfo::class)->times(1)->make()->
        each(function ($base_info) use ($faker, $user_ids) {
            $base_info->user_id = $faker->randomElement($user_ids);
        });

        \App\Models\UserBaseInfo::insert($base_info->toArray());
    }
}
