<?php

namespace App\Listeners;

use App\Events\UserFollowEvent;
use App\Friend;
use Storage;
use Log;

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

        if ($friend->avatar_file_name !== null)
        {
            // get the original avatar img
            $avatarUrl = $friend->avatar->url('original');
            $avatarFileName = $friend->avatar_file_name;
            $avatarLocalStoragePath = 'tmp/' . $avatarFileName;
            try
            {
                if ( !$avatarUrl )
                    throw new \Exception('avatar URL is empty');

                // download from S3 and save locally
                $avatarData = @file_get_contents($avatarUrl);
                if ( !$avatarData )
                    throw new \Exception("Couldn't download old file");

                Storage::disk('local')->put($avatarLocalStoragePath, $avatarData);
                $avatarTmpPath = storage_path('app/') . $avatarLocalStoragePath;

                if ( !file_exists( $avatarTmpPath ) )
                    throw new \Exception('Local tmp file does not exist');

                // just attach a local file
                $friend_model->avatar = $avatarTmpPath;

            } catch (\Exception $ex)
            {
                Log::error('Could not copy avatar: ' . $avatarUrl . ', ' . $ex->getMessage());
            }
        }
        $user = $event->getFollower();
        $friend_model->friend()->associate($friend);
        $user->friends()->save($friend_model);
    }
}
