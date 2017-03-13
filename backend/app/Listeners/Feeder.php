<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\Feedable;
use App\Events\OnAddToCalendar;
use App\Events\OnAlbumPhotoComment;
use App\Events\OnSpotRemind;
use App\Events\OnPlanRemind;
use App\Events\OnSpotCreate;
use App\Events\OnSpotComment;
use App\Events\OnSpotCommentDelete;
use App\Events\OnSpotUpdate;
use App\Events\OnUserBirthday;
use App\Events\OnWallMessage;
use App\Events\OnWallPostDelete;
use App\Events\OnWallPostDislike;
use App\Events\OnWallPostLike;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;

use App\Feed;
use App\Services\Privacy;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class Feeder
 * Create new feed for the user
 * @package App\Listeners
 */
class Feeder implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * @var Guard
     */
    private $auth;

    /**
     * Feeder constructor.
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\Event $event
     */
    public function handle(Event $event)
    {
        switch (true) {
            case $event instanceof UserFollowEvent:
                $following = $event->getFollowing();
                $this->addFeed($event, $event->getFollower()->followers->reject(function ($item) use ($following) {
                    return $item->id === $following->id;
                }));
                $this->addFeed($event, $following);
                break;
            case $event instanceof UserUnfollowEvent:
                $this->addFeed($event, $event->getFollowing());
                break;
            case $event instanceof OnAddToCalendar:
                $this->addFeed($event, $event->user->followers);
                break;
            case $event instanceof OnSpotRemind:
                $this->addFeed($event, $event->spot->favorites);
                break;
            case $event instanceof OnPlanRemind:
                break;
            case $event instanceof OnWallMessage:
                if ($event->isSelf()) {
                    $this->addFeed($event, $event->wall->sender->followers);
                }
                break;
            case $event instanceof OnWallPostDelete:
                $this->addFeed($event, $event->wall->sender);
                break;
            case $event instanceof OnWallPostDislike:
                $this->addFeed($event, $event->wall->sender);
                break;
            case $event instanceof OnWallPostLike:
                $this->addFeed($event, $event->wall->sender);
                break;
            case $event instanceof OnSpotCreate:
                if (!empty($event->spot) && !empty($event->spot->user) && $event->spot->user->privacy_events !== Privacy::NOBODY) {
                    $this->addFeed($event, $event->spot->user->followers);
                }
                break;
            case $event instanceof OnSpotUpdate:
                if (!empty($event->spot) && !empty($event->spot->user) && $event->spot->user->privacy_events !== Privacy::NOBODY) {
                    $this->addFeed($event, $event->spot->user->followers);
                }
                break;
            case $event instanceof OnSpotComment:
                $this->addFeed($event, $event->comment->commentable->user);
                break;
            case $event instanceof OnSpotCommentDelete:
                $this->addFeed($event, $event->comment->sender);
                break;
            case $event instanceof OnAlbumPhotoComment:
                $this->addFeed($event, $event->comment->commentable->album->user);
                break;
            case $event instanceof OnUserBirthday:
                $this->addFeed($event, $event->user->followers);
                break;
        }
    }

    /**
     * Add new feed
     *
     * @param Feedable $event
     * @param \App\User|\Illuminate\Database\Eloquent\Collection $users
     *
     * @return void
     */
    protected function addFeed(Feedable $event, $users)
    {
        if ($users instanceof Collection) {
            foreach ($users as $user) {
                $feed = new Feed(['event_type' => class_basename($event)]);
                $feed->feedable()->associate($event->getFeedable());
                $feed->sender()->associate($event->getFeedSender());

                if ($user->id !== $this->auth->id()) {
                    $user->feeds()->save($feed);
                }
            }
        } elseif ($users instanceof User and $users->id !== $this->auth->id()) {
            $feed = new Feed(['event_type' => class_basename($event)]);
            $feed->feedable()->associate($event->getFeedable());
            $feed->sender()->associate($event->getFeedSender());

            $users->feeds()->save($feed);
        }
    }
}
