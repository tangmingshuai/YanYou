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

        $faker=app(Faker\Generator::class);
        $base_info=factory(\App\Models\UserBaseInfo::class)->times(10)->make()->
        each(function ($base_info) use ($faker) {
        });

        \App\Models\UserBaseInfo::insert($base_info->toArray());
    }
}
