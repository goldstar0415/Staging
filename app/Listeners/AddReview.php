<?php

namespace App\Listeners;

use App\AlbumPhoto;
use App\Events\OnComment;
use App\SpotPhoto;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddReview /*implements ShouldQueue*/
{
//    use InteractsWithQueue;
    /**
     * Handle the event.
     *
     * @param  OnComment  $event
     * @return void
     */
    public function handle(OnComment $event)
    {
        $user_id = null;

        $commentable = $event->comment->commentable;
        if ($commentable instanceof AlbumPhoto) {
            $user_id = $commentable->album->user_id;
        } elseif ($commentable instanceof SpotPhoto) {
            $user_id = $commentable->spot->user_id;
        } else {
            $user_id = $commentable->user_id;
        }

        User::find($user_id)->reviews()->save($event->comment);
    }
}
