<?php

namespace App\Providers;

use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Listeners\AddFriend;
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
        ],
        UserUnfollowEvent::class => [
            RemoveFriend::class,
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

        //
    }
}
