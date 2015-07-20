<?php

use App\Album;
use App\AlbumPhoto;
use Illuminate\Database\Seeder;
use Seeds\FileSeeder;

class AlbumPhotosTableSeeder extends Seeder
{
    use FileSeeder;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Album::all()->each(
            function (Album $album) {
                $photos = factory(AlbumPhoto::class, mt_rand(1, 10))->make();
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
                if ($photos instanceof AlbumPhoto) {
                    $this->saveModelFile(
                        $photos,
                        \Faker\Factory::create()->image(storage_path('app'))
                    );
                } else {
                    $photos->each(function (AlbumPhoto $photo) {
                        $this->saveModelFile(
                            $photo,
                            \Faker\Factory::create()->image(storage_path('app'))
                        );
                    });
                }
            }
        );
    }
}
