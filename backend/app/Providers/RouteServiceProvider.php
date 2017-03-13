<?php

namespace App\Providers;

use App\ActivityCategory;
use App\ActivityLevel;
use App\Album;
use App\AlbumPhoto;
use App\Blog;
use App\BlogCategory;
use App\BloggerRequest;
use App\Comment;
use App\Area;
use App\ChatMessage;
use App\ContactUs;
use App\Friend;
use App\Plan;
use App\Social;
use App\Spot;
use App\SpotOwnerRequest;
use App\SpotPhoto;
use App\SpotReport;
use App\SpotTypeCategory;
use App\User;
use App\Wall;
use Auth;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Request;
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
     * @param  \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $router->patterns([
            'users' => '^[\w\-_]*$',
            'albums' => '\d+',
            'photos' => '\d+',
            'spots' => '\d+',
            'friends' => '\d+',
            'message' => '\d+',
            'areas' => '\d+',
            'comments' => '\d+',
            'wall' => '\d+',
            'plans' => '\d+',
            'posts' => '^[\w\-_]*$',
            'activitylevel' => '\d+',
            'social' => '\w+',
            'post_slug' => '\w+'
        ]);

        $router->model('albums', Album::class);
        $router->bind('photos', function ($value) {
            if (Request::is('photos/*')) {
                return AlbumPhoto::findOrFail($value);
            } elseif (Request::is('spots/*')) {
                return SpotPhoto::findOrFail($value);
            }

            return $value;
        });
        $router->bind('users', function ($value) {
            if (preg_match(User::$aliasRule, $value)) {
                return User::where('alias', $value)->firstOrFail();
            }

            return User::findOrFail($value);
        });
        $router->model('friends', Friend::class);
        $router->bind('spots', function ($value) {
            $spot = Spot::withRequested()->findOrFail($value);
            if ($spot->is_approved === false and
                Auth::check() and
                $spot->user_id !== Auth::id() and
                !Auth::user()->hasRole('admin')) {
                throw new NotFoundHttpException;
            }

            return $spot;
        });
        $router->model('message', ChatMessage::class);
        $router->model('areas', Area::class);
        $router->model('comments', Comment::class);
        $router->model('wall', Wall::class);
        $router->model('plans', Plan::class);
        $router->bind('posts', function ($value) {

            $blog_post = Blog::where(is_numeric($value)?'id':'slug', $value)->first();

            if ($blog_post === null) {
                throw new NotFoundHttpException;
            }

            return $blog_post;
        });

        // Admin

        $router->model('activitylevel', ActivityLevel::class);
        $router->bind('social', function ($value) {
            return Social::where('name', $value)->first();
        });
        $router->model('spot-categories', SpotTypeCategory::class);
        $router->model('spot-reports', SpotReport::class);
        $router->model('blog-categories', BlogCategory::class);
        $router->model('activity-categories', ActivityCategory::class);
        $router->model('blogger-request', BloggerRequest::class);
        $router->model('contact-us', ContactUs::class);
        $router->model('owner_request', SpotOwnerRequest::class);

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
        $router->group(['prefix' => 'admin',
            'middleware' => 'admin',
            'namespace' => $this->namespace . '\Admin'
        ],
        function ($router) {
            require app_path('Http/admin_routes.php');
        });
    }
}
