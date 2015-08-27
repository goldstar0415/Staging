<?php

use App\Spot;
use App\SpotComment;
use App\User;
use Illuminate\Database\Seeder;

class SpotCommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Spot::random(10)->get()->each(function (Spot $spot) {
            $comments_count = mt_rand(2, 6);
            /**
             * @var \Illuminate\Database\Eloquent\Collection $users
             */
            $users = User::random($comments_count)->get();
            $votes = factory(SpotComment::class, $comments_count)
                ->make()
                ->each(
                    function (SpotComment $spot_comment) use ($spot, $users) {
                        $spot_comment->spot()->associate($spot);
                        $spot_comment->user()->associate($users->shift());
                    }
                );
            $spot->votes()->saveMany($votes);
        });
    }
}
