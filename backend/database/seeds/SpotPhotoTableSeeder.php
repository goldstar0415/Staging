<?php

use App\Spot;
use App\SpotPhoto;
use Illuminate\Database\Seeder;

class SpotPhotoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Spot::all()->each(function (Spot $spot) {
            $photos = factory(SpotPhoto::class, mt_rand(1, 10))->make();
            if ($photos instanceof SpotPhoto) {
                $spot->photos()->save($photos);
            } else {
                $spot->photos()->saveMany($photos);
            }
        });
    }
}
