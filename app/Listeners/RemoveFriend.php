<?php

namespace App\Listeners;

use App\Events\UserUnfollowEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveFriend
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserUnfollowEvent  $event
     * @return void
     */
    public function handle(UserUnfollowEvent $event)
    {
        $event->getFollower()
            ->friends()
            ->where('friend_id', $event->getFollowing()->id)
            ->first()
            ->delete();
    }
}
