<?php

namespace App\Listeners;

use App\Events\UserFollowEvent;
use App\Friend;
use File;

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
        $avatar_copy_path = storage_path('tmp/') . $friend->avatar_file_name;
        File::copy($friend->avatar->path(), $avatar_copy_path);
        $friend_model = new Friend([
            'avatar' => $avatar_copy_path,
            'first_name' => $friend->first_name,
            'last_name' => $friend->last_name,
            'birth_date' => $friend->birth_date,
            'email' => $friend->email,
            'address' => $friend->address,
            'location' => $friend->location
        ]);
        $user = $event->getFollower();
        $friend_model->friend()->associate($friend);
        $user->friends()->save($friend_model);
    }
}
