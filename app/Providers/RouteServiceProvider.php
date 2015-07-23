<?php

namespace App\Providers;

use App\Album;
use App\AlbumPhoto;
use App\AlbumPhotoComment;
use App\User;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        $router->model('albums', Album::class);
        $router->model('photos', AlbumPhoto::class);
        $router->model('users', User::class);
        
        $router->bind('comments', function ($value) use ($router) {
            if (explode('.', $router->currentRouteName())[0] === 'photos') {
                $comment = AlbumPhotoComment::where('id', $value)->first();
                if ($comment) {
                    return $comment;
                } else {
                    throw new NotFoundHttpException;
                }
            }
            return null;
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
