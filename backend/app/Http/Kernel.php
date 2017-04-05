<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Barryvdh\Cors\HandleCors::class,
        \App\Http\Middleware\JsonPayloadParse::class,
        \App\Http\Middleware\CheckOnline::class,
        \App\Http\Middleware\ValidProxies::class,
//      \App\Http\Middleware\VerifyCsrfToken::class, TODO: uncomment after development
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'base64upload' => \App\Http\Middleware\Base64Upload::class,
        'privacy' => \App\Http\Middleware\PrivacyCheck::class,
        'blogger' => \App\Http\Middleware\BloggerCheck::class,
        'admin' => \App\Http\Middleware\AdminCheck::class,
        'bloggerOrAdmin' => \App\Http\Middleware\BloggerOrAdmin::class,
        'throttle' => \GrahamCampbell\Throttle\Http\Middleware\ThrottleMiddleware::class,
    ];
}
