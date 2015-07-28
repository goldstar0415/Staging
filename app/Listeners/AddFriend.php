<?php

namespace App\Listeners;

use App\Events\UserFollowEvent;
use App\Friend;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddFriend
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
     * @param  UserFollowEvent  $event
     * @return void
     */
    public function handle(UserFollowEvent $event)
    {
        $friend = $event->getFollowing();
        $friend_model = new Friend([
            'avatar' => $friend->avatar->url(),
            'first_name' => $friend->first_name,
            'last_name' => $friend->last_name,
            'birth_date' => $friend->birth_date->format(config('app.date_format')),
            'email' => $friend->email,
            'address' => $friend->address,
            'location' => $friend->location
        ]);
        $user = $event->getFollower();
        $friend_model->friend()->associate($friend);
        $user->friends()->save($friend_model);
    }
}
