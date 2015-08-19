<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\Feedable;
use App\Events\OnAddToCalendar;
use App\Events\OnAlbumPhotoComment;
use App\Events\OnSpotRemind;
use App\Events\OnPlanRemind;
use App\Events\OnSpotCreate;
use App\Events\OnSpotReview;
use App\Events\OnSpotReviewDelete;
use App\Events\OnSpotUpdate;
use App\Events\OnUserBirthday;
use App\Events\OnWallMessage;
use App\Events\OnWallPostDelete;
use App\Events\OnWallPostDislike;
use App\Events\OnWallPostLike;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;

use App\Feed;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Feeder /*implements ShouldQueue*/
{
//    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param \App\Events\Event $event
     */
    public function handle(Event $event)
    {
        switch (true) {
            case $event instanceof UserFollowEvent:
                $this->addFeed($event, $event->getFollower()->followers);
                $this->addFeed($event, $event->getFollowing());
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
                $this->addFeed($event, $event->wall->sender->followers);
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
                $this->addFeed($event, $event->spot->user->followers);
                break;
            case $event instanceof OnSpotUpdate:
                $this->addFeed($event, $event->spot->user->followers);
                break;
            case $event instanceof OnSpotReview:
                $this->addFeed($event, $event->review->spot->user);
                break;
            case $event instanceof OnSpotReviewDelete:
                $this->addFeed($event, $event->review->user);
                break;
            case $event instanceof OnAlbumPhotoComment:
                $this->addFeed($event, $event->comment->photo->album->user);
                break;
            case $event instanceof OnUserBirthday:
                $this->addFeed($event, $event->user->followers);
                break;
        }
    }

    /**
     * @param Feedable $event
     * @param \App\User|\Illuminate\Database\Eloquent\Collection $users
     *
     * @return void
     */
    protected function addFeed(Feedable $event, $users)
    {
        $feed = new Feed(['event_type' => class_basename($event)]);
        $feed->feedable()->associate($event->getFeedable());
        $feed->sender()->associate($event->getFeedSender());

        if ($users instanceof Collection) {
            foreach ($users as $user) {
                $user->feeds()->save($feed);
            }
        } elseif ($users instanceof User) {
            $users->feeds()->save($feed);
        }
    }
}
