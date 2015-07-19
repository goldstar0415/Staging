<?php

use App\Blog;
use App\BlogComment;
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
            $comments = factory(BlogComment::class, mt_rand(1, 10))->make();
            if ($comments instanceof BlogComment) {
                $user = User::random()->first();
                $comments->blog()->associate($blog);
                $comments->user()->associate($user);
                $blog->comments()->save($comments);
            } else {
                $comments->each(function (BlogComment $comment) use ($blog) {
                    $user = User::random()->first();
                    $comment->blog()->associate($blog);
                    $comment->user()->associate($user);
                });
                $blog->comments()->saveMany($comments);
            }
        });
    }
}
