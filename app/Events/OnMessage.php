<?php

namespace App\Events;

use App\ChatMessage;
use App\Events\Event;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OnMessage extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $user;

    public $message;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param ChatMessage $message
     */
    public function __construct(User $user, ChatMessage $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user.' . $this->user->random_hash];
    }
}
