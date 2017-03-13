<?php

namespace App\Events;

/**
 * Class Event
 * @package App\Events
 *
 * Base event class
 */
abstract class Event
{
    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['pmessage'];
    }
}
