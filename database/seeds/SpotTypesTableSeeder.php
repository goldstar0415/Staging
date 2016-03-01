<?php

use App\SpotType;
use Illuminate\Database\Seeder;


class SpotTypesTableSeeder extends Seeder
{
    public function run()
    {
        factory(SpotType::class, 'event')->create();
        factory(SpotType::class, 'todo')->create();
        factory(SpotType::class, 'food')->create();
        factory(SpotType::class, 'shelter')->create();
    }
}
