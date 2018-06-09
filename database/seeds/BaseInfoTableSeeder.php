<?php

use App\Models\UserBaseInfo;
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
        $base_info=factory(UserBaseInfo::class)->times(10)->make()->
        each(function ($base_info) use ($faker) {
        });

        UserBaseInfo::insert($base_info->toArray());
    }
}
