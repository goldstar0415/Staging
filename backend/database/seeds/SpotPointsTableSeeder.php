<?php

use Illuminate\Database\Seeder;
use App\SpotPoint;
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
            $points = factory(SpotPoint::class, mt_rand(1, 5))->create(['spot_id' => $spot->id]);
        });
    }
}
