<?php

namespace App\Services\EmailChange;

use Illuminate\Support\ServiceProvider;

class EmailChangeServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEmailBroker();

        $this->registerTokenRepository();
    }

    /**
     * Register the email broker instance.
     *
     * @return void
     */
    protected function registerEmailBroker()
    {
        $this->app->singleton([EmailChangeBroker::class => 'auth.email'], function ($app) {
            // The password token repository is responsible for storing the email addresses
            // and password reset tokens. It will be used to verify the tokens are valid
            // for the given e-mail addresses. We will resolve an implementation here.
            $tokens = $app['auth.email.tokens'];

            $view = $app['config']['auth.email.view'];

            // The password broker uses a token repository to validate tokens and send user
            // password e-mails, as well as validating that password reset process as an
            // aggregate service of sorts providing a convenient interface for resets.
            return new EmailChangeBroker(
                $tokens, $app['mailer'], $view
            );
        });
    }

    /**
     * Register the token repository implementation.
     *
     * @return void
     */
    protected function registerTokenRepository()
    {
        $this->app->singleton([TokenRepositoryInterface::class => 'auth.email.tokens'], function ($app) {
            $connection = $app['db']->connection();

            // The database token repository is an implementation of the token repository
            // interface, and is responsible for the actual storing of auth tokens and
            // their e-mail addresses. We will inject this table and hash key to it.
            $table = $app['config']['auth.email.table'];

            $key = $app['config']['app.key'];

            $expire = $app['config']->get('auth.email.expire', 60);

            return new DatabaseTokenRepository($connection, $table, $key, $expire);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['auth.email', 'auth.email.tokens', TokenRepositoryInterface::class, EmailChangeBroker::class];
    }
}
