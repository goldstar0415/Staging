<?php

namespace App\Events;

use App\Wall;
use Illuminate\Queue\SerializesModels;

class OnWallPostDelete extends Event implements Feedable
{
    use SerializesModels;
    /**
     * @var Wall
     */
    public $wall;

    /**
     * Create a new event instance.
     *
     * @param Wall $wall
     */
    public function __construct(Wall $wall)
    {
        $this->wall = $wall;
    }

    public function getFeedable()
    {
        return $this->wall;
    }

    public function getFeedSender()
    {
        return $this->wall->sender;
    }
}
