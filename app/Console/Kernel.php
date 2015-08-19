<?php

namespace App\Console;

use App\Events\OnUserBirthday;
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
        $schedule->command('inspire')
                 ->hourly();
        $schedule->call(function () {
             $users = User::whereRaw(
                 "date_part('day', birth_date) = date_part('day', CURRENT_DATE) + 1
                 and date_part('month', birth_date) = date_part('month', CURRENT_DATE)"
             );

            foreach ($users as $user) {
                event(new OnUserBirthday($user));
            }
        })->daily();
    }
}
