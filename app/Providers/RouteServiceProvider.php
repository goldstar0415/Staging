<?php

namespace App\Providers;

use App\Album;
use App\AlbumPhoto;
use App\PhotoComment;
use App\Area;
use App\ChatMessage;
use App\Friend;
use App\Spot;
use App\SpotReview;
use App\User;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $router->model('albums', Album::class);
        $router->model('photos', AlbumPhoto::class);
        $router->model('users', User::class);
        $router->model('friends', Friend::class);
        $router->model('spots', Spot::class);
        $router->model('message', ChatMessage::class);
        $router->model('selection', Area::class);
        $router->model('reviews', SpotReview::class);

        $router->bind('comments', function ($value) use ($router) {
            $comment = null;
            if (Request::is('photos/*')) {
                $comment = PhotoComment::findOrFail($value);
            }

            return $comment;
        });

        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
