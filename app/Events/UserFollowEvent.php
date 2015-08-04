<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;

class UserFollowEvent extends Event
{
    use SerializesModels;

    protected $follower;
    protected $following;

    /**
     * Create a new event instance.
     *
     * @param \App\User $follower
     * @param \App\User $following
     */
    public function __construct(User $follower, User $following)
    {
        $this->follower = $follower;
        $this->following = $following;
    }

    /**
     * @return User
     */
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * @return User
     */
    public function getFollowing()
    {
        return $this->following;
    }
}
