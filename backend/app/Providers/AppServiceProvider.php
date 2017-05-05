<?php

namespace App\Providers;

use App\AlbumPhoto;
use App\Area;
use App\BloggerRequest;
use App\Comment;
use App\Events\OnComment;
use App\Events\OnSpotCreate;
use App\Exceptions\TokenException;
use App\Extensions\Validations;
use App\Http\Controllers\SocialContactsController;
use App\Link;
use App\Plan;
use App\Role;
use App\Services\Attachments;
use App\Services\Privacy;
use App\Services\Social\GoogleClient;
use App\Services\Social\GoogleToken;
use App\Spot;
use App\SpotType;
use App\User;
use Config;
use Illuminate\Support\ServiceProvider;
use Request;
use Validator;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Carbon\Carbon::setToStringFormat('Y-m-d h:i:s a');
        $this->modelsEvents();

//        \DB::listen(function ($query, $bindings) {
//            \Log::info('QUERY: ' . $query, $bindings);
//        }); //TODO: delete
        
        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new Validations($translator, $data, $rules, $messages);
        });

        view()->composer('admin.spots.index', function ($view) {
            $categories = SpotType::categoriesList();
            $view->with('spot_categories', $categories);
        });

        // override request scheme for URL-generator using a special front nginx header
        if ( $realScheme = $this->app['request']->header('X-Real-Scheme') ) {
            $realScheme = strtolower($realScheme);
            if (in_array($realScheme, ['http', 'https'])) {
                URL::forceSchema($realScheme);
            } else {
                throw new \Exception('Invalid X-Real-Scheme header, check front-nginx config');
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laracasts\Generators\GeneratorsServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        } else if ($this->app->environment('production')) {
            $this->app->register(\Sentry\SentryLaravel\SentryLaravelServiceProvider::class);
        }

        $this->app->register(\GrahamCampbell\Throttle\ThrottleServiceProvider::class);
        $this->app->bind(Privacy::class, function ($app) {
            return new Privacy($app[\Illuminate\Contracts\Auth\Guard::class]);
        });
        $this->app->bind(Attachments::class, function ($app) {
            return new Attachments($app['request']);
        });
        $this->app->bind(GoogleClient::class, function ($app) {
            if (!$app['session']->has('google_token') and !$app['request']->has('code')) {
                throw new TokenException('No saved token.');
            } elseif ($app['request']->has('code')) {
                $contacts_engine = GoogleClient::getContactsEngine();
                $app['session']->put(
                    'google_token',
                    serialize(new GoogleToken(
                        $contacts_engine->provider->getAccessToken($app['request']->get('code')),
                        $contacts_engine->scopes
                    ))
                );
            }
            $token = unserialize($app['session']->get('google_token'));

            return new GoogleClient($token);
        });
    }

    protected function modelsEvents()
    {
        Comment::created(function (Comment $comment) {
            event(new OnComment($comment));
        });

        User::creating(function (User $user) {
            $user->alias = str_slug($user->full_name);
            $pattern = "^{$user->alias}([0-9]*)?$";
            $latest_slug = User::where('alias', '~', $pattern)->latest('id')->pluck('alias');
            if ($latest_slug) {
                preg_match('/' . $pattern . '/', $latest_slug, $pieces);

                $number = intval(end($pieces));

                $user->alias .= ($number + 1);
            }
            $user->random_hash = str_random();
            $user->token = str_random(30);
        });

        BloggerRequest::updated(function (BloggerRequest $request) {
            switch ($request->status) {
                case 'accepted':
                    $user = $request->user;
                    if (!$user->hasRole('blogger')) {
                        $user->roles()->attach(Role::take('blogger'));
                    }
                    break;
            }
        });

        Link::deleting(function (Link $link) {
            $link->cleanAttached();
        });
        Spot::deleting(function (Spot $spot) {
            $spot->comments()->delete();
            $spot->amenities_objects()->delete();
            $spot->remotePhotos()->delete();
            $spot->votes()->delete();
            $spot->points()->delete();
            $spot->photos()->delete();
            $spot->cleanAttached();
        });
        Plan::deleting(function (Plan $plan) {
            $plan->cleanAttached();
        });
        AlbumPhoto::deleting(function (AlbumPhoto $albumPhoto) {
            $albumPhoto->cleanAttached();
        });
        Area::deleting(function (Area $area) {
            $area->cleanAttached();
        });
    }
}
