<?php

namespace App\Listeners;

use App\Events\UserUnfollowEvent;

/**
 * Class RemoveFriend
 * Listener removes the user from friends when unfollow
 * @package App\Listeners
 */
class RemoveFriend
{
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
