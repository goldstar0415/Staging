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

                if ($exist_user) {
                    $this->auth->login($exist_user);

                    if (!$auth_user->socials()->where('name', $social->name)->exists()) {
                        $this->attachSocial($auth_user, $social, $user->getId());
                    }

                    return redirect(frontend_url());
                }
                //Checks if account exists with social email
                $exist_user = User::where('email', $user->getEmail())->first();

                if ($exist_user) {
                    $this->auth->login($exist_user);
                    $this->attachSocial($social, $auth_user, $user->getId());

                    return redirect(frontend_url());
                }
                //If account for current social data doesn't exists
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

            if ($this->getUserByKey($social, $user->getId())) {
                return response()->json(['message' => 'Somebody already attached this social'], 403);
            }

            $this->attachSocial($request->user(), $social, $user->getId());

            return redirect(frontend_url('settings'));
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

    /**
     * @param Social $social
     * @param integer $key
     * @return mixed
     */
    protected function getUserByKey($social, $key)
    {
        return $social->users()->wherePivot('social_key', $key)->first();
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isConfirmed(Request $request)
    {
        return $request->has('code') or $request->has('state');
    }

    /**
     * @param Social $social
     * @param User $user
     * @param string $key
     */
    private function attachSocial($user, $social, $key)
    {
        $user->socials()->attach($social, ['social_key' => $key]);
    }
}
