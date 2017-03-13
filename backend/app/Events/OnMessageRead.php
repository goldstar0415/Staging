<?php

namespace App\Events;

use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class OnMessageRead
 * @package App\Events
 *
 * Fires on read chat message
 */
class OnMessageRead extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $sender_id;

    private $receiver;

    /**
     * Create a new event instance.
     *
     * @param $sender_id
     * @param $receiver_id
     */
    public function __construct($sender_id, $receiver_id)
    {
        $this->sender_id = $sender_id;
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
