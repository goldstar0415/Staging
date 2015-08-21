<?php

namespace App\Events;

use App\Wall;
use Illuminate\Queue\SerializesModels;

class OnWallMessage extends Event implements Feedable
{
    use SerializesModels;

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

    public function isSelf()
    {
        return $this->wall->sender->id === $this->wall->receiver->id;
    }
}
