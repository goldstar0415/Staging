<?php

namespace App\Events;

use App\Spot;
use App\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class OnAddToCalendar
 * @package App\Events
 *
 * Fires when adds spot to calendar
 */
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

    /**
     * {@inheritDoc}
     *
     * @return Spot
     */
    public function getFeedable()
    {
        return $this->spot;
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
