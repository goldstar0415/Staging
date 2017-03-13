<?php

namespace App\Http\Controllers;

use App\Role;
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
        /** @var \Laravel\Socialite\Contracts\User $socialUser */
        /** @var User $newUser */
        /** @var \Illuminate\Http\JsonResponse $errResponse */

        $provider = Socialite::with($social->name);
        if ($social->name === 'facebook') {
            $provider->scopes(['email', 'user_friends']);
        }

        if ($this->isConfirmed($request)) {
            if ($error = $this->checkError($request)) {
                return $error;
            }

            $socialUser = $provider->user();
            if (!$socialUser or !$socialUser->getEmail()) {
                return self::getRedirectProviderError();
            }

            if (!$this->auth->check()) {

                if ($redirectResponse = $this->checkExistingUser($social, $socialUser)) {
                    return $redirectResponse;
                }

                $newUser = $this->createUser($social, $socialUser);
                $this->auth->login($newUser);

                return self::getRedirectSuccess($newUser);
            }

            if ($errResponse = $this->checkSocialAccount($request, $social, $socialUser)) {
                return $errResponse;
            }

            $this->attachSocial($request->user(), $social, $socialUser->getId());

            return self::getRedirectSettings();
        }

        return $provider->redirect();
    }

    /**
     * Does user already exist, login and redirect if does
     * @param Social $social
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return false|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function checkExistingUser($social, $socialUser)
    {
        //Checks by social identifier if user exists, auth if does
        /** @var User $existingUser */
        $existingUser = $this->getUserByKey($social, $socialUser->getId());
        if ($existingUser) {
            $this->auth->login($existingUser);
            return self::getRedirectSuccess($existingUser);
        }

        //Checks if account exists with social email, auth and attach current social if does
        /** @var User $existingUser */
        $existingUser = $this->getUserByEmail($socialUser->getEmail());
        if ($existingUser) {
            $this->auth->login($existingUser);
            $this->attachSocial($this->auth->user(), $social, $socialUser->getId());
            return self::getRedirectSuccess($existingUser);
        }
        return false;
    }

    /**
     * Check if user is trying to attach his/her social account to the existing one
     * or if someone already attached current social account
     * @param Request $request
     * @param Social $social
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return false|\Illuminate\Http\JsonResponse
     */
    private function checkSocialAccount(Request $request, Social $social, $socialUser)
    {
        if ($request->user()->socials()->where('name', $social->name)->exists()) {
            return response()->json(
                ['message' => 'User already attached ' . $social->display_name . ' social'],
                403
            );
        }
        if ($this->getUserByKey($social, $socialUser->getId())) {
            return response()->json(['message' => 'Somebody already attached this social'], 403);
        }

        return false;
    }

    /**
     * Save a new social user
     * @param Social $social
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return User
     */
    private function createUser($social, $socialUser)
    {
        $name = $socialUser->getName();
        list($first_name, $last_name) = str_contains($name, ' ') ? explode(' ', $name) : [$name, null];
        $newUser = User::create([
            'email' => $socialUser->getEmail(),
            'first_name' => $first_name,
            'last_name' => $last_name,
            'avatar' => self::getLargeAvatarUrl( $social->name, $socialUser->getAvatar() ),
            'verified' => true
        ]);
        $this->attachSocial($newUser, $social, $socialUser->getId());
        $newUser->roles()->attach(Role::take(config('entrust.default')));

        return $newUser;
    }

    /**
     * Create a redirect response on provider error
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private static function getRedirectProviderError()
    {
        return redirect()->away(frontend_url() . '?' . http_build_query([
            'auth_error' => [
                'error' => 'No user data'
            ]
        ]));
    }

    /**
     * Create a success redirect
     * @param $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private static function getRedirectSuccess($user)
    {
        return redirect()->away(frontend_url($user->alias ?: $user->id));
    }

    /**
     * Get a redirect to the settings page
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private static function getRedirectSettings()
    {
        return redirect()->away(frontend_url('settings'));
    }

    /**
     * @param string $email
     * @return User
     */
    private function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * Get a large avatar URL
     * @param $socialName
     * @param $sourceUrl
     * @return string
     */
    final protected static function getLargeAvatarUrl($socialName, $sourceUrl)
    {
        if ( !$sourceUrl )
            return '';
        $s = 720; // max avatar size we have to request via any OAuth API
        switch ( $socialName ) {
            case 'facebook':
                $segments = explode('?', $sourceUrl);
                return implode('?', [reset($segments), "type=large&width={$s}&height={$s}"]);
            case 'google':
                $segments = explode('?', $sourceUrl);
                return implode('?', [reset($segments), "sz={$s}"]);
            default:
                return $sourceUrl;
        }
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
