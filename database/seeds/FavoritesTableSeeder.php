<?php

use App\Favorite;
use App\Spot;
use App\User;
use Illuminate\Database\Seeder;

class FavoritesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(50)->get()->each(function (User $user) {
            $favorites_count = mt_rand(1, 10);
            $favorites = factory(Favorite::class, $favorites_count)->make();
            $spots = Spot::random($favorites_count)->get();
            if ($favorites instanceof Favorite) {
                $favorites->spot()->associate($spots->pop());
                $favorites->user()->associate($user);
                $user->favorites()->save($favorites);
            } else {
                $favorites->each(function (Favorite $favorite) use ($user, $spots) {
                    $favorite->spot()->associate($spots->pop());
                    $favorite->user()->associate($user);
                });
                $user->favorites()->saveMany($favorites);
            }
        });
    }
}
