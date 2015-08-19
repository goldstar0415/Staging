<?php

namespace App\Events;

use App\Spot;
use App\User;
use Illuminate\Queue\SerializesModels;

class OnAddToCalendar extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var Spot
     */
    public $spot;
    /**
     * @var User
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Spot $spot
     */
    public function __construct(User $user, Spot $spot)
    {
        $this->spot = $spot;
        $this->user = $user;
    }

    public function getFeedable()
    {
        return $this->spot;
    }

    /**
     * @inheritdoc
     */
    public function getFeedSender()
    {
        return $this->user;
    }
}
