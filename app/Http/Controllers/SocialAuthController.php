<?php

namespace App\Http\Controllers;

use App\Social;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Http\Requests;
use Socialite;

/**
 * Class SocialAuthController
 * @package App\Http\Controllers
 *
 * Provide social auth logic
 */
class SocialAuthController extends Controller
{
    /**
     * @var Guard auth provider instance
     */
    private $auth;

    /**
     * SocialAuthController constructor. Has dependency on Guard contract
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * If hasn't social network response, redirects user to social auth page
     * else make account with information from social network
     * @param Request $request
     * @param Social $social
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getAccount(Request $request, $social)
    {
        $provider = Socialite::with($social->name);

        if ($this->isConfirmed($request)) {
            /**
             * @var \Laravel\Socialite\Contracts\User $user
             */
            $user = $provider->user();

            if (!$user) {
                abort(400);
            }

            if (!$this->auth->check()) {
                //Checks if user exists by social identifier
                $exist_user = $this->getUserByKey($social, $user->id);
                $auth_user = $this->auth->user();
                //Checks if exists user with current social data then auth them
                if ($exist_user) {
                    $this->auth->login($exist_user);

                    return redirect(frontend_url());
                }
                //Checks if account exists with social email then auth them and attach current social
                $exist_user = User::where('email', $user->getEmail())->first();

                if ($exist_user) {
                    $this->auth->login($exist_user);
                    $this->attachSocial($social, $auth_user, $user->getId());

                    return redirect(frontend_url());
                }
                //If account for current social data doesn't exists - create new account
                list($first_name, $last_name) = explode(' ', $user->getName());
                $new_user = User::create([
                    'email' => $user->getEmail(),
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'avatar' => $user->getAvatar()
                ]);
                $this->attachSocial($new_user, $social, $user->getId());
                $this->auth->login($new_user);

                return redirect(frontend_url());
            }
            //If attach social for existing account
            if ($request->user()->socials()->where('name', $social->name)->exists()) {
                return response()->json(
                    ['message' => 'User already attached ' . $social->display_name . ' social'],
                    403
                );
            }
            //Checks if someone already attached current social account
            if ($this->getUserByKey($social, $user->getId())) {
                return response()->json(['message' => 'Somebody already attached this social'], 403);
            }

            $this->attachSocial($request->user(), $social, $user->getId());

            return redirect(frontend_url('settings'));
        }

        return $provider->redirect();
    }

    /**
     * Detach social account for user
     *
     * @param Request $request
     * @param \App\Social $social
     * @return array
     */
    public function deleteAccount(Request $request, $social)
    {
        return ['result' => (bool)$request->user()->socials()->detach($social->id)];
    }

    /**
     * Get user by unique social identifier
     *
     * @param Social $social
     * @param integer $key
     * @return mixed
     */
    protected function getUserByKey($social, $key)
    {
        return $social->users()->wherePivot('social_key', $key)->first();
    }

    /**
     * Checks has response data from social network
     *
     * @param Request $request
     * @return bool
     */
    protected function isConfirmed(Request $request)
    {
        return $request->has('code') or $request->has('state');
    }

    /**
     * Attach social account for current user account
     *
     * @param Social $social
     * @param User $user
     * @param string $key
     */
    private function attachSocial($user, $social, $key)
    {
        $user->socials()->attach($social, ['social_key' => $key]);
    }
}
