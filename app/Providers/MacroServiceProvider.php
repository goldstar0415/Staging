<?php

namespace App\Providers;

use App\Services\ICalendar;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param ResponseFactory $factory
     */
    public function boot(ResponseFactory $factory)
    {
        $factory->macro('ical', function ($user) use ($factory) {

            return $factory->make(with(new ICalendar())->makeForUser($user), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => sprintf('attachment; filename=%s_%s', env('APP_NAME'), ICalendar::FILE_NAME)
            ]);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
