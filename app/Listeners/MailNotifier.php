<?php

namespace App\Listeners;

use App\Events\Event;
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

class MailNotifier implements ShouldQueue
{
    use InteractsWithQueue;

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
                if ($event->isSelf()) {
                    $this->send($event->getFeedSender()->followers->filter(function ($follower) {
                        return $follower->notification_wall_post;
                    }), 'wall-post', ['sender' => $event->getFeedSender(), 'wall' => $event->wall]);
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
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @param string $view
     * @param array $data
     */
    private function send(Collection $users, $view, array $data)
    {
        $users->each(function ($user) use ($view, $data) {
            $this->mailer->send(['text' => 'emails.' . $view], $data, function ($message) use ($user) {
                /**
                 * @var \Illuminate\Mail\Message $message
                 */
                $message->to($user->email, $user->first_name);
            });
        });
    }
}
