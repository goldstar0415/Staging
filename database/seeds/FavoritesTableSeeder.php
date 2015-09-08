<?php

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
        User::random(12)->get()->each(function (User $user) {
            $favorites_count = mt_rand(1, 10);
            $spots_ids = Spot::random($favorites_count)->get()->pluck('id')->toArray();
            $user->favorites()->sync($spots_ids);
        });
    }
}
