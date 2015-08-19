<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;

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

    public function getFeedable()
    {
        return $this->user;
    }

    public function getFeedSender()
    {
        return $this->user;
    }
}
