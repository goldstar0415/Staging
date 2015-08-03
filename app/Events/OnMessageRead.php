<?php

namespace App\Events;

use App\Events\Event;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OnMessageRead extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $receiver_id;

    private $receiver;

    /**
     * Create a new event instance.
     *
     * @param $receiver_id
     */
    public function __construct($receiver_id)
    {
        $this->receiver_id = $receiver_id;
        $this->receiver = User::find($receiver_id);
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user.' . $this->receiver->random_hash];
    }
}
