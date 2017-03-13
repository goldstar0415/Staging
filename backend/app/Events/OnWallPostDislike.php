<?php

namespace App\Events;

use App\Wall;
use App\WallRate;
use Illuminate\Queue\SerializesModels;

/**
 * Class OnWallPostDislike
 * @package App\Events
 *
 * Fires on wall post dislike
 */
class OnWallPostDislike extends Event implements Feedable
{
    use SerializesModels;

    /**
     * @var Wall
     */
    public $wall;
    /**
     * @var WallRate
     */
    public $rate;

    /**
     * Create a new event instance.
     *
     * @param Wall $wall
     * @param WallRate $rate
     */
    public function __construct(Wall $wall, WallRate $rate)
    {
        $this->wall = $wall;
        $this->rate = $rate;
    }

    /**
     * {@inheritDoc}
     *
     * @return Wall
     */
    public function getFeedable()
    {
        return $this->rate->wall;
    }

    /**
     * {@inheritDoc}
     *
     * @return \App\User
     */
    public function getFeedSender()
    {
        return $this->rate->user;
    }
}
