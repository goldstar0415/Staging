<?php

namespace App\Events;

use App\ChatMessage;
use App\User;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class OnMessage
 * @package App\Events
 *
 * Fires on sending chat message
 */
class OnMessage extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $user;

    public $message;

    private $receiver_hash;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param ChatMessage $message
     * @param $receiver_hash
     */
    public function __construct(User $user, ChatMessage $message, $receiver_hash)
    {
        $this->user = $user;
        $this->message = $message;
        $this->receiver_hash = $receiver_hash;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user.' . $this->receiver_hash];
    }
}
