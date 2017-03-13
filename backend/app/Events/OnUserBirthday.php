<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class OnUserBirthday
 * @package App\Events
 *
 * Fires when one day before some user's birthday
 */
class OnUserBirthday extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritDoc}
     *
     * @return User
     */
    public function getFeedable()
    {
        return $this->user;
    }

    /**
     * {@inheritDoc}
     *
     * @return User
     */
    public function getFeedSender()
    {
        return $this->user;
    }
}
