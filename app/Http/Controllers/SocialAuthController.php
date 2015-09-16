<?php

namespace App\Http\Controllers;

use App\Social;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Http\Requests;
use Socialite;

class SocialAuthController extends Controller
{

    protected $accepts_socials;

    private $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('guest');
    }

    public function getAccount(Request $request, $social)
    {
        $provider = Socialite::with($social->name);

        if ($request->has('code') or $request->has('state')) {
            /**
             * @var \Laravel\Socialite\Contracts\User $user
             */
            $user = $provider->user();

            if (!$user) {
                abort(400);
            }
            $exist_user = $social->users()->wherePivot('token', $user->token)->first();

            if ($exist_user) {
                $this->auth->login($exist_user);

                if ($this->auth->user()->socials()->where('name', $social)->first() === null) {
                    $this->auth->user()->socials()->attach($social, ['token' => $user->token]);
                }

                return redirect(config('app.frontend_url'));
            } else {
                list($first_name, $last_name) = explode(' ', $user->getName());
                $new_user = User::create(
                    [
                        'email' => $user->getEmail(),
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'avatar' => $user->getAvatar(),
                        'random_hash' => str_random()
                    ]
                );
                $new_user->socials()->attach($social, ['token' => $user->token]);
                $this->auth->login($new_user);

                return redirect(config('app.frontend_url'));
            }
        } else {
            return $provider->redirect();
        }
    }

    /**
     * @param Request $request
     * @param \App\Social $social
     * @return array
     */
    public function deleteAccount(Request $request, $social)
    {
        $request->user()->socials()->detach($social->id);

        return ['message' => true];
    }
}
