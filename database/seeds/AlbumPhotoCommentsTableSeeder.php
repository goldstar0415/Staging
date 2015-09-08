<?php

use App\AlbumPhoto;
use App\Comment;
use App\User;
use Illuminate\Database\Seeder;

class AlbumPhotoCommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AlbumPhoto::all()->each(function (AlbumPhoto $photo) {
            $comments = factory(Comment::class, mt_rand(1, 10))->make();
            if ($comments instanceof Comment) {
                $user = User::random()->first();
                $comments->commentable()->associate($photo);
                $comments->sender()->associate($user);
                $photo->comments()->save($comments);
            } else {
                $comments->each(function (Comment $comment) use ($photo) {
                    $user = User::random()->first();
                    $comment->commentable()->associate($photo);
                    $comment->sender()->associate($user);
                });
                $photo->comments()->saveMany($comments);
            }
        });
    }
}
