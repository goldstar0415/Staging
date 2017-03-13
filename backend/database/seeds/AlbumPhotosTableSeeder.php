<?php

use App\Album;
use App\AlbumPhoto;
use Illuminate\Database\Seeder;

class AlbumPhotosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Album::all()->each(
            function (Album $album) {
                $photos = factory(AlbumPhoto::class, mt_rand(1, 3))->make();
                if ($photos instanceof AlbumPhoto) {
                    $photos->album()->associate($album);
                    $album->photos()->save($photos);
                } else {
                    $photos->each(
                        function (AlbumPhoto $photo) use ($album) {
                            $photo->album()->associate($album);
                        }
                    );
                    $album->photos()->saveMany($photos);
                }
            }
        );
    }
}
