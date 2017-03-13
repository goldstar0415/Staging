<?php

use App\Events\UserFollowEvent;
use Illuminate\Database\Seeder;
use App\User;

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
            $followings_count = mt_rand(3, 17);
            $followings = User::where('id', '!=', $user->id)->random($followings_count)->get();
            for ($i = 0; $i < $followings_count; $i++) {
                $follow_user = $followings->pop();
                $user->followings()->attach($follow_user);

                event(new UserFollowEvent($user, $follow_user));
            }
        });
    }
}
