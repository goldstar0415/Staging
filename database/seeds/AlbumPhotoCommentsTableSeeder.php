<?php

use App\AlbumPhoto;
use App\AlbumPhotoComment;
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
            $comments = factory(AlbumPhotoComment::class, mt_rand(1, 10))->make();
            if ($comments instanceof AlbumPhotoComment) {
                $user = User::random()->first();
                $comments->photo()->associate($photo);
                $comments->user()->associate($user);
                $photo->comments()->save($comments);
            } else {
                $comments->each(function (AlbumPhotoComment $comment) use ($photo) {
                    $user = User::random()->first();
                    $comment->photo()->associate($photo);
                    $comment->user()->associate($user);
                });
                $photo->comments()->saveMany($comments);
            }
        });
    }
}
