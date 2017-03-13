<?php

use App\Spot;
use App\Comment;
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
            $comments = factory(Comment::class, $comments_count)
                ->make()
                ->each(
                    function (Comment $spot_comment) use ($spot, $users) {
                        $spot_comment->commentable()->associate($spot);
                        $spot_comment->sender()->associate($users->shift());
                    }
                );
            $spot->comments()->saveMany($comments);
        });
    }
}
