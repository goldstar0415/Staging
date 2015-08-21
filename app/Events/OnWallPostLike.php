<?php

namespace App\Events;

use App\Wall;
use App\WallRate;
use Illuminate\Queue\SerializesModels;

class OnWallPostLike extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var Wall
     */
    public $wall;
    /**
     * @var WallRate
     */
    private $rate;

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

    public function getFeedable()
    {
        return $this->rate->wall;
    }

    public function getFeedSender()
    {
        return $this->rate->user;
    }
}
