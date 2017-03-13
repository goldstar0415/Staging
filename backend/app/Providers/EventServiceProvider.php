<?php

namespace App\Providers;

use App\Events\OnComment;
use App\Events\OnMessage;
use App\Events\OnWallMessage;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Events\OnAddToCalendar;
use App\Events\OnSpotCreate;
use App\Events\OnSpotUpdate;
use App\Events\OnUserBirthday;
use App\Events\OnSpotComment;
use App\Events\OnSpotCommentDelete;
use App\Events\OnWallPostDelete;
use App\Events\OnWallPostLike;
use App\Events\OnWallPostDislike;
use App\Events\OnSpotRemind;
use App\Events\OnPlanRemind;
use App\Events\OnAlbumPhotoComment;
use App\Events\UserInviteEvent;
use App\Listeners\AddFriend;
use App\Listeners\AddReview;
use App\Listeners\Feeder;
use App\Listeners\MailNotifier;
use App\Listeners\RemoveFriend;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserFollowEvent::class => [
            AddFriend::class,
            Feeder::class,
            MailNotifier::class
        ],
        UserUnfollowEvent::class => [
            RemoveFriend::class,
            Feeder::class,
            MailNotifier::class
        ],
        UserInviteEvent::class => [
            MailNotifier::class
        ],
        OnWallPostDelete::class => [
            Feeder::class
        ],
        OnAddToCalendar::class => [
            Feeder::class
        ],
        OnSpotCreate::class => [
            Feeder::class,
            MailNotifier::class
        ],
        OnSpotUpdate::class => [
            Feeder::class
        ],
        OnUserBirthday::class => [
            Feeder::class
        ],
        OnSpotComment::class => [
            Feeder::class
        ],
        OnAlbumPhotoComment::class => [
            Feeder::class
        ],
        OnSpotCommentDelete::class => [
            Feeder::class
        ],
        OnWallPostLike::class => [
            Feeder::class
        ],
        OnWallPostDislike::class => [
            Feeder::class
        ],
        OnSpotRemind::class => [
            Feeder::class,
            MailNotifier::class
        ],
        OnPlanRemind::class => [
            Feeder::class
        ],
        OnComment::class => [
            AddReview::class
        ],
        OnMessage::class => [
            MailNotifier::class
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
    }
}
