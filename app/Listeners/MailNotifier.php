<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\OnMessage;
use App\Events\OnSpotCreate;
use App\Events\OnSpotRemind;
use App\Events\OnWallMessage;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\Eloquent\Collection;
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
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        switch (true) {
            case $event instanceof OnMessage:
                $event = unserialize(serialize($event));//TODO: remove when fix
                $this->send($event->message->receiver, 'message', [
                    'sender' => $event->message->sender()->first(),
                    'receiver' => $event->message->receiver()->first()
                ]);
                break;
            case $event instanceof UserFollowEvent:
                $sender = $event->getFeedSender();
                $following = $event->getFollowing();
                $this->send($event->getFollower()->followers->filter(function ($follower) {
                    return $follower->notification_follow;
                }), 'follow', compact('sender', 'following'));
                break;
            case $event instanceof UserUnfollowEvent:
                $sender = $event->getFeedSender();
                $following = $event->getFollowing();
                $this->send($event->getFollower()->followers->filter(function ($follower) {
                    return $follower->notification_follow;
                }), 'unfollow', compact('sender', 'following'));
                break;
            case $event instanceof OnWallMessage:
                if (!$event->isSelf()) {
                    $receiver = $event->wall->receiver;
                    if ($receiver->notification_wall_post) {
                        $this->send($receiver, 'wall-post', [
                            'sender' => $event->getFeedSender(), 'wall' => $event->wall
                        ]);
                    }
                }
                break;
            case $event instanceof OnSpotCreate:
                $this->send($event->getFeedSender()->followers->filter(function ($follower) {
                    return $follower->notification_new_spot;
                }), 'spot', ['sender' => $event->getFeedSender(), 'spot' => $event->spot]);
                break;
            case $event instanceof OnSpotRemind:
                $this->send($event->spot->calendarUsers->filter(function ($user) {
                    return $user->notification_coming_spot;
                }), 'coming_spot', ['sender' => $event->getFeedSender(), 'spot' => $event->spot]);
                break;
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection|User $user_data
     * @param string $view
     * @param array $data
     */
    private function send($user_data, $view, array $data)
    {
        if ($user_data instanceof Collection) {
            $user_data->each(function ($user) use ($view, $data) {
                $this->sendMail($user, $view, $data);
            });
        } elseif ($user_data instanceof User) {
            $this->sendMail($user_data, $view, $data);
        }
    }

    /**
     * @param $user_data
     * @param $view
     * @param array $data
     */
    private function sendMail($user_data, $view, array $data)
    {
        $this->mailer->send('emails.' . $view, $data, function ($message) use ($user_data) {
            /**
             * @var \Illuminate\Mail\Message $message
             */
            $message->to($user_data->email, $user_data->first_name);
        });
    }
}
