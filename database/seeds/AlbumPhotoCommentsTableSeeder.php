<?php

use App\AlbumPhoto;
use App\PhotoComment;
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
            $comments = factory(PhotoComment::class, mt_rand(1, 10))->make();
            if ($comments instanceof PhotoComment) {
                $user = User::random()->first();
                $comments->commentable()->associate($photo);
                $comments->user()->associate($user);
                $photo->comments()->save($comments);
            } else {
                $comments->each(function (PhotoComment $comment) use ($photo) {
                    $user = User::random()->first();
                    $comment->commentable()->associate($photo);
                    $comment->user()->associate($user);
                });
                $photo->comments()->saveMany($comments);
            }
        });
    }
}
