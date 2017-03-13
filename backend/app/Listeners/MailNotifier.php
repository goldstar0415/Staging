<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\OnMessage;
use App\Events\OnSpotCreate;
use App\Events\OnSpotRemind;
use App\Events\OnWallMessage;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Events\UserInviteEvent;
use App\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class MailNotifier
 * @package App\Listeners
 *
 * Listener for mail notify users
 */
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
                $sender = $event->message->sender()->first();
                $this->send($event->message->receiver, 'message', [
                    'sender' => $event->message->sender()->first(),
                ], 'New message');
                break;
            case $event instanceof UserFollowEvent:
                $sender = $event->getFeedSender();
                $subject = 'Following feed';
                $following = $event->getFollowing();
                $event->getFollower()->followers->filter(function ($follower) use ($following) {
                    return $follower->notification_follow and $follower->id !== $following->id;
                })->each(function ($follower) use ($sender, $following, $subject) {
                    $this->send($follower, 'follow', ['sender' => $sender, 'following' => $following], $subject);
                });
                if ($following->notification_follow) {
                    $this->send($following, 'follow-you', ['sender' => $sender], $subject);
                }
                break;
            case $event instanceof UserUnfollowEvent:
                $sender = $event->getFeedSender();
                $subject = 'UnFollowing feed';
                $following = $event->getFollowing();
                $event->getFollower()->followers->filter(function ($follower) use ($following) {
                    return $follower->notification_follow and $follower->id !== $following->id;
                })->each(function ($follower) use ($sender, $following, $subject) {
                    $this->send($follower, 'unfollow', ['sender' => $sender, 'following' => $following], $subject);
                });
                if ($following->notification_follow) {
                    $this->send($following, 'unfollow-you', [ 'sender' => $sender], $subject);
                }
                break;
            case $event instanceof UserInviteEvent:
                $sender = $event->getFeedSender();
                $subject = 'Invitation to Zoomtivity';
                $newUser = new User();
                $newUser->email = $event->getEmail();
                $_username = $sender->first_name && $sender->last_name ? "{$sender->first_name} {$sender->last_name} ({$sender->email})" : "{$sender->email}";
                $this->send($newUser, 'invite', ['sender' => $sender, 'text' => "User {$_username} wants to invite you to Zoomtivity"], $subject);
                break;
            case $event instanceof OnWallMessage:
                if (!$event->isSelf()) {
                    $receiver = $event->wall->receiver;
                    $sender = $event->getFeedSender();
                    if ($receiver->notification_wall_post) {
                        $this->send($receiver, 'wall-post', [
                            'sender' => $sender,
                            'wall' => $event->wall,
                        ], 'New message on the wall');
                    }
                }
                break;
            case $event instanceof OnSpotCreate:
                $sender = $event->getFeedSender();
                if(!empty($sender) && !empty($sender->followers))
                {
                    $this->send($sender->followers->filter(function ($follower) {
                        return $follower->notification_new_spot;
                    }), 'spot', ['sender' => $sender, 'spot' => $event->spot], 'New event');
                }
                break;
            case $event instanceof OnSpotRemind:
                $sender = $event->getFeedSender();
                $this->send($event->spot->calendarUsers->filter(function ($user) {
                    return $user->notification_coming_spot;
                }), 'coming_spot', ['sender' => $sender, 'spot' => $event->spot, 'user' => $event->spot->calendarUsers()->first()], 'Spot remind');
                break;
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection|User $user_data
     * @param string $view
     * @param array $data
     * @param string $subject
     */
    private function send($user_data, $view, array $data, $subject)
    {
        if ($user_data instanceof Collection) {
            $user_data->each(function ($user) use ($view, $data, $subject) {
                $this->sendMail($user, $view, array_merge($data, ['user' => $user, 'token' => $user->token] ), $subject);
            });
        } elseif ($user_data instanceof User) {
            $this->sendMail($user_data, $view, array_merge($data, ['user' => $user_data, 'token' => $user_data->token] ), $subject);
        }
    }

    /**
     * @param $user_data
     * @param $view
     * @param array $data
     * @param string $subject
     */
    private function sendMail($user_data, $view, array $data, $subject)
    {
        $this->mailer->send('emails.' . $view, $data, function ($message) use ($user_data, $subject) {
            /**
             * @var \Illuminate\Mail\Message $message
             */
            $message->to($user_data->email, $user_data->first_name);
            $message->subject($subject);
        });
    }
}
