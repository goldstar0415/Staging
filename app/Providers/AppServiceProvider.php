<?php

namespace App\Providers;

use App\Extensions\Validations;
use App\Services\Attachments;
use App\Services\Privacy;
use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new Validations($translator, $data, $rules, $messages);
        });

        \DB::listen(function ($sql, $bindings, $time) {
            \Log::info($sql, $bindings);
        });//TODO: delete after development
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
        $this->app->bind(Attachments::class, function ($app) {
            return new Attachments($app['request']);
        });
    }
}
