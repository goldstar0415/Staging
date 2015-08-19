<?php

namespace App\Providers;

use App\Events\OnWallMessage;
use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Events\OnAddToCalendar;
use App\Events\OnSpotCreate;
use App\Events\OnSpotUpdate;
use App\Events\OnUserBirthday;
use App\Events\OnSpotReview;
use App\Events\OnSpotReviewDelete;
use App\Events\OnWallPostDelete;
use App\Events\OnWallPostLike;
use App\Events\OnWallPostDislike;
use App\Events\OnSpotRemind;
use App\Events\OnPlanRemind;
use App\Events\OnAlbumPhotoComment;

use App\Listeners\AddFriend;
use App\Listeners\Feeder;
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
            Feeder::class
        ],
        UserUnfollowEvent::class => [
            RemoveFriend::class,
            Feeder::class
        ],
        OnWallMessage::class => [
            Feeder::class
        ],
        OnWallPostDelete::class => [
            Feeder::class
        ],
        OnAddToCalendar::class => [
            Feeder::class
        ],
        OnSpotCreate::class => [
            Feeder::class
        ],
        OnSpotUpdate::class => [
            Feeder::class
        ],
        OnUserBirthday::class => [
            Feeder::class
        ],
        OnSpotReview::class => [
            Feeder::class
        ],
        OnAlbumPhotoComment::class => [
            Feeder::class
        ],
        OnSpotReviewDelete::class => [
            Feeder::class
        ],
        OnWallPostLike::class => [
            Feeder::class
        ],
        OnWallPostDislike::class => [
            Feeder::class
        ],
        OnSpotRemind::class => [
            Feeder::class
        ],
        OnPlanRemind::class => [
            Feeder::class
        ]
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

        //
    }
}
