<?php

use Illuminate\Database\Seeder;


class SpotTypesTableSeeder extends Seeder
{
    public function run()
    {
        factory(App\SpotType::class, 'event')->create();
        factory(App\SpotType::class, 'recreation')->create();
        factory(App\SpotType::class, 'pitstop')->create();
    }
}
