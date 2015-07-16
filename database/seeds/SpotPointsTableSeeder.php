<?php

use Illuminate\Database\Seeder;

class SpotPointsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\Spot::all()->each(function (App\Spot $spot) {
            $spot->points()->saveMany(factory(App\SpotPoint::class, mt_rand(1, 5))->make()->all());
        });
    }
}
