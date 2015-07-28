<?php

namespace App\Providers;

use App\Services\Privacy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register(\Laracasts\Generators\GeneratorsServiceProvider::class);
        }
        $this->app->bind(Privacy::class, function ($app) {
            return new Privacy($app['request']->user(), $app['auth']);
        });
    }
}
