<?php

use App\Blog;
use App\BlogComment;
use App\Comment;
use App\User;
use Illuminate\Database\Seeder;

class BlogCommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Blog::all()->each(function (Blog $blog) {
            $comments = factory(Comment::class, mt_rand(1, 10))->make();
            if ($comments instanceof Comment) {
                $user = User::random()->first();
                $comments->commentable()->associate($blog);
                $comments->sender()->associate($user);
                $blog->comments()->save($comments);
            } else {
                $comments->each(function (Comment $comment) use ($blog) {
                    $user = User::random()->first();
                    $comment->commentable()->associate($blog);
                    $comment->sender()->associate($user);
                });
                $blog->comments()->saveMany($comments);
            }
        });
    }
}
