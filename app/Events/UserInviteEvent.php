<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;
use Log;
/**
 * Class UserUnfollowEvent
 * @package App\Events
 *
 * Fires on user unfollow somebody
 */
class UserInviteEvent extends Event implements Feedable
{
    use SerializesModels;

    /**
     * @var User
     */
    protected $inviter;
    /**
     * @var User
     */
    protected $email;

    /**
     * Create a new event instance.
     *
     * @param \App\User $inviter
     * @param string $stringEmail
     */
    public function __construct(User $inviter, $stringEmail)
    {
        Log::debug('UserInviteEvent constructor');
        $this->inviter  = $inviter;
        $this->email    = $stringEmail;
    }

    /**
     * Get follower
     *
     * @return User
     */
    public function getInviter()
    {
        return $this->inviter;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     *
     * @return User
     */
    public function getFeedable()
    {
        return $this->getInviter();
    }

    /**
     * {@inheritDoc}
     *
     * @return User
     */
    public function getFeedSender()
    {
        return $this->getInviter();
    }
}
