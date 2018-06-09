<?php

use App\Models\UserTargetInfo;
use Illuminate\Database\Seeder;

class TargetInfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker=app(Faker\Generator::class);
        $target_info=factory(UserTargetInfo::class)->times(10)->make()->
        each(function ($target_info) use ($faker) {
        });

        \App\Models\UserTargetInfo::insert($target_info->toArray());
    }
}
