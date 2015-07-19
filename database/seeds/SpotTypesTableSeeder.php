<?php

use App\SpotType;
use Illuminate\Database\Seeder;


class SpotTypesTableSeeder extends Seeder
{
    public function run()
    {
        factory(SpotType::class, 'event')->create();
        factory(SpotType::class, 'recreation')->create();
        factory(SpotType::class, 'pitstop')->create();
    }
}
