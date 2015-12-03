<?php

namespace App\Console;

use App\Events\OnSpotRemind;
use App\Events\OnUserBirthday;
use App\GeneratedUser;
use App\Mailers\AppMailer;
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
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        $mailer = app(AppMailer::class);
        // Check coming birthdays and spots
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

        $schedule->call(function () use ($mailer) {
            $users = GeneratedUser::with(['user' => function ($query) {
                $query->where('verified', true);
            }])->get();

            foreach ($users as $user) {
                $mailer->remindGeneratedUser($user->user, $user->password);
            }
        })->cron('* 0 * *  0/2');
    }
}
