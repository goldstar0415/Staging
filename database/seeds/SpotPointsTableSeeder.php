<?php

use App\SpotPoint;
use Illuminate\Database\Seeder;
use App\Spot;

class SpotPointsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Spot::all()->each(function (Spot $spot) {
            $spot->points()->saveMany(factory(SpotPoint::class, mt_rand(1, 5))->make()->all());
        });
    }
}
