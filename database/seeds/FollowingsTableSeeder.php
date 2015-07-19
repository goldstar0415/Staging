<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Following;

class FollowingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::all()->each(function (User $user) {
            $followings_count = mt_rand(5, 20);
            $followings = User::where('id', '!=', $user->id)->random($followings_count)->get();
            for ($i = 0; $i < $followings_count; $i++) {
                $following = new Following();
                $following->follower()->associate($user);
                $following->following()->associate($followings->pop());
                $user->followings()->save($following);
            }
        });
    }
}
