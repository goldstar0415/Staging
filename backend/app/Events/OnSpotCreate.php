<?php

namespace App\Events;

use App\Spot;
use Illuminate\Queue\SerializesModels;

/**
 * Class OnSpotCreate
 * @package App\Events
 *
 * Fires on spot create
 */
class OnSpotCreate extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var Spot Spot model
     */
    public $spot;

    /**
     * Create a new event instance.
     *
     * @param Spot $spot Spot model
     */
    public function __construct(Spot $spot)
    {
        $this->spot = $spot;
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
     * @return \App\User
     */
    public function getFeedSender()
    {
        return $this->spot->user;
    }
}
