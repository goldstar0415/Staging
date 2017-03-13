<?php

use App\Album;
use App\User;
use Illuminate\Database\Seeder;

class AlbumTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(10)->get()->each(function (User $user) {
            $albums = factory(Album::class, mt_rand(1, 5))->make();
            if ($albums instanceof Album) {
                $albums->user()->associate($user);
                $user->albums()->save($albums);
            } else {
                $albums->each(
                    function (Album $album) use ($user) {
                        $album->user()->associate($user);
                    }
                );
                $user->albums()->saveMany($albums);
            }
        });
    }
}
