<?php

namespace Main;


use Illuminate\Support\ServiceProvider;
use Main\DownloadModule\Download;

class DownloadServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!$this->app->routesAreCached()) {
            require __DIR__ . '/routes.php';
        }
        app('html')->macro('download', function ($file, $tile = '', $attributes = [], $userfiles = true) {
            $d = new Download($file, $tile, $attributes, $userfiles);
            return $d->htmLink();
        });
        app('html')->macro('dlink', function ($file, $userfiles = true) {
            $d = new Download($file, $tile = '', $attributes = [], $userfiles);
            return $d->link();
        });
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
    }

}