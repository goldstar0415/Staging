<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserFollowEvent
 * @package App\Events
 *
 * Fires on user follow somebody
 */
class UserFollowEvent extends Event implements Feedable
{
    use SerializesModels;

    /**
     * @var User
     */
    protected $follower;
    /**
     * @var User
     */
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
     * Get follower user
     *
     * @return User
     */
    public function getFollower()
    {
        return $this->follower;
    }

    /**
     * Get following user
     *
     * @return User
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * {@inheritDoc}
     *
     * @return User
     */
    public function getFeedable()
    {
        return $this->getFollowing();
    }

    /**
     * {@inheritDoc}
     *
     * @return User
     */
    public function getFeedSender()
    {
        return $this->getFollower();
    }
}
