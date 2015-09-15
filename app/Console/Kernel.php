<?php

namespace App\Console;

use App\Events\OnSpotRemind;
use App\Events\OnUserBirthday;
use App\Spot;
use App\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\AppInstallCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            
            $users = User::comingBirthday()->get();

            foreach ($users as $user) {
                event(new OnUserBirthday($user));
            }

            $spots = Spot::coming()->get();
            $spots->each(function (Spot $spot) {
                event(new OnSpotRemind($spot));
            });
        })->daily();
    }
}
