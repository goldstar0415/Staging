<?php

use App\User;
use App\Wall;
use Illuminate\Database\Seeder;

class WallsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(10)->get()->each(function (User $user) {
            $wall_posts = factory(Wall::class, mt_rand(3, 10))->make()->each(
                function (Wall $wall) {
                    $sender = User::random()->first();
                    $wall->sender()->associate($sender);
                }
            );
            $user->walls()->saveMany($wall_posts);
        });
    }
}
