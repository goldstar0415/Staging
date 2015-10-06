<?php

namespace App\Listeners;

use App\Events\UserFollowEvent;
use App\Friend;
use File;

/**
 * Class AddFriend
 * Listener adds new friend to the user, when follow somebody
 * @package App\Listeners
 */
class AddFriend
{
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
            'first_name' => $friend->first_name,
            'last_name' => $friend->last_name,
            'birth_date' => $friend->birth_date,
            'email' => $friend->email,
            'address' => $friend->address,
            'location' => $friend->location
        ]);
        if ($friend->avatar_file_name !== null) {
            $avatar_copy_path = storage_path('tmp/') . $friend->avatar_file_name;
            File::copy($friend->avatar->path(), $avatar_copy_path);
            $friend_model->avatar = $avatar_copy_path;
        }
        $user = $event->getFollower();
        $friend_model->friend()->associate($friend);
        $user->friends()->save($friend_model);
    }
}
