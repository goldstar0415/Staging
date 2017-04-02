<?php

namespace App\Listeners;

use App\Events\UserFollowEvent;
use App\User;

/**
 * Class AddFriend
 * Listener adds new friend to the user, when follow somebody
 * @package App\Listeners
 */
class AddFriend
{
    /**
     * Handle the event.
     * Check if this is the beginning of great friendship
     *
     * @param  UserFollowEvent  $event
     * @return void
     */
    public function handle(UserFollowEvent $event)
    {
	    /** @var User $user */
	    /** @var User $followUser */

		$user = $event->getFollower();
		$followUser = $event->getFollowing();

	    if ( $followUser->followings()->find($user->id) ) {

		    // add a friend for follower

		    if ( !$user->friends()->find($followUser->id) ) {

			    $user->friends()->attach([$followUser->id], [
				    'first_name' => $followUser->first_name,
				    'last_name' => $followUser->last_name
			    ]);

		    }

		    // add a friend for following

		    if ( !$followUser->friends()->find($user->id) ) {

			    $followUser->friends()->attach($user->id, [
				    'first_name' => $user->first_name,
				    'last_name' => $user->last_name
			    ]);

		    }

	    }

    }
}
