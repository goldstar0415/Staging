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
     * SocialAuthController constructor. Register Guard contract dependency
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * If there is no response from the social network, redirect the user to the social auth page
     * else make create with information from social network
     * @param Request $request
     * @param Social $social
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getAccount(Request $request, $social)
    {
        $provider = Socialite::with($social->name);

        if ($this->isConfirmed($request)) {
            if ($error = $this->checkError($request)) {
                return $error;
            }
            /**
             * @var \Laravel\Socialite\Contracts\User $user
             */
            $user = $provider->user();
            if (!$user) {
                abort(400);
            }

            if (!$this->auth->check()) {
                //Checks by social identifier if user exists
                $exist_user = $this->getUserByKey($social, $user->getId());

                //Checks if user exists with current social identifier, auth if does
                if ($exist_user) {
                    $this->auth->login($exist_user);

                    return redirect()->away(frontend_url($exist_user->alias ?: $exist_user->id));
                }

                //Checks if account exists with social email, auth and attach current social if does
                $exist_user = User::where('email', $user->getEmail())->first();

                if ($exist_user) {
                    $this->auth->login($exist_user);
                    $this->attachSocial($this->auth->user(), $social, $user->getId());

                    return redirect()->away(frontend_url($exist_user->alias ?: $exist_user->id));
                }

                //If account for current social data doesn't exist - create new one
                list($first_name, $last_name) = explode(' ', $user->getName());
                $new_user = User::create([
                    'email' => $user->getEmail(),
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'avatar' => $user->getAvatar()
                ]);
                $this->attachSocial($new_user, $social, $user->getId());
                $this->auth->login($new_user);

                return redirect()->away(frontend_url($new_user->alias ?: $new_user->id));
            }

            //Check if user is trying to attach his/her social account to the existing one
            if ($request->user()->socials()->where('name', $social->name)->exists()) {
                return response()->json(
                    ['message' => 'User already attached ' . $social->display_name . ' social'],
                    403
                );
            }

            //If someone already attached current social account
            if ($this->getUserByKey($social, $user->getId())) {
                return response()->json(['message' => 'Somebody already attached this social'], 403);
            }

            $this->attachSocial($request->user(), $social, $user->getId());

            return redirect()->away(frontend_url('settings'));
        }

        return $provider->redirect();
    }

    /**
     * Detaches social account for user
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
     * Gets user by unique social identifier
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
     * Checks if the user was redirected from social network
     *
     * @param Request $request
     * @return bool
     */
    protected function isConfirmed(Request $request)
    {
        return $request->has('code') or $request->has('state');
    }

    /**
     * Attaches social account to current user account
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param Social $social
     * @param string $key
     */
    private function attachSocial($user, $social, $key)
    {
        $user->socials()->attach($social, ['social_key' => $key]);
    }

    protected function checkError(Request $request)
    {
        if ($request->has('error')) {
            $response = [
                'auth_error' => [
                    'error' => $request->error
                ]
            ];
            $response['auth_error']['description'] = $request->has('error_description') ? $request->error_description : null;
            $response['auth_error']['reason'] = $request->has('error_reason') ? $request->error_reason : null;
            $response['auth_error']['code'] = $request->has('error_code') ? $request->error_code : null;

            return redirect()->away(frontend_url() . '?' . http_build_query($response));
        }

        return null;
    }
}
