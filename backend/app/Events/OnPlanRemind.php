<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class OnPlanRemind extends Event implements Feedable
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getFeedable()
    {
        // TODO: Implement getFeedable() method.
    }

    public function getFeedSender()
    {

    }
}
