<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\OnSpotCreate;
use App\Events\OnSpotRemind;
use App\Events\OnWallMessage;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailNotifier
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * Create the event listener.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(Event $event)
    {
        switch (true) {
            case $event instanceof UserFollowEvent:
                $event->getFollower()->followers->each(function ($follower) {
                    /**
                     * @var \App\User $follower
                     */

                    if ($follower->notification_follow) {
//                        $this->send(, $text);
                    }
                });
                break;
            case $event instanceof UserUnfollowEvent:
                break;
            case $event instanceof OnWallMessage:
                break;
            case $event instanceof OnSpotCreate:
                break;
            case $event instanceof OnSpotRemind:
                break;
        }
    }

    /**
     * @param string $email
     * @param string $text
     */
    private function send($email, $text)
    {
        $this->mailer->send(['text' => 'notify'], ['text' => $text], function ($message) use ($email) {
            $message->to($email);
        });
    }
}
