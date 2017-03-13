<?php

namespace App\Providers;

use App\Contracts\CalendarExportable;
use App\Services\ICalendar;
use App\User;
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
        $factory->macro('ical', function (CalendarExportable $model) use ($factory) {
            $content = '';

            if ($model instanceof User) {
                $content = with(new ICalendar())->makeForUser($model);
            } else {
                $calendar = new ICalendar();
                $calendar->getCalendar()->addComponent($model->export());
                $content = $calendar->getCalendar()->render();
            }

            return $factory->make($content, 200, [
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
