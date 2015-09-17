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
    }

    /**
     * @param Request $request
     * @param Social $social
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getAccount(Request $request, $social)
    {
        $provider = Socialite::with($social->name);

        if ($request->has('code') or $request->has('state')) {
            /**
             * @var \Laravel\Socialite\Contracts\User $user
             */
            $user = $provider->user();

            if (!$this->auth->check()) {

                if (!$user) {
                    abort(400);
                }
                $exist_user = $social->users()->wherePivot('social_key', $user->id)->first();

                if ($exist_user) {
                    $this->auth->login($exist_user);

                    if (!$this->auth->user()->socials()->where('name', $social->name)->exists()) {
                        $this->auth->user()->socials()->attach($social, ['social_key' => $user->id]);
                    }

                    return redirect(frontend_url());
                }

                if (User::where('email', $user->getEmail())->exists()) {
                    return response()->json(['message' => 'User already exists with this email'], 400);
                }

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
                $new_user->socials()->attach($social, ['social_key' => $user->id]);
                $this->auth->login($new_user);

                return redirect(frontend_url());
            }

            $user_socials = $request->user()->socials();

            if ($user_socials->where('name', $social->name)->exists()) {
                return response()->json(
                    ['message' => 'User already attached ' . $social->display_name . ' social'],
                    403
                );
            }

            $user_socials->attach($social, ['social_key' => $user->id]);

            return redirect(frontend_url());
        }

        return $provider->redirect();
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
