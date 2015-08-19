<?php

namespace App\Events;

use App\Spot;
use Illuminate\Queue\SerializesModels;

class OnSpotRemind extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var Spot
     */
    public $spot;

    /**
     * Create a new event instance.
     *
     * @param Spot $spot
     */
    public function __construct(Spot $spot)
    {
        $this->spot = $spot;
    }

    public function getFeedable()
    {
        return $this->spot;
    }

    public function getFeedSender()
    {
        return $this->spot->user;
    }
}
