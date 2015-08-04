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
        foreach (Social::all('name')->toArray() as $row) {
            $this->accepts_socials[] = $row['name'];
        }
        $this->middleware('guest');
    }

    public function getAccount(Request $request, $social)
    {
        if (!in_array($social, $this->getAcceptsSocials())) {
            abort(400);
        }

        $provider = Socialite::with($social);

        if ($request->has('code') or $request->has('state')) {
            /**
             * @var \Laravel\Socialite\Contracts\User $user
             */
            $user = $provider->user();

            if (!$user) {
                abort(400);
            }

            $exist_user = User::where('email', $user->getEmail())->first();

            if ($exist_user) {
                $this->auth->login($exist_user);
            } else {
                list($first_name, $last_name) = explode(' ', $user->getName());
                $new_user = User::create(
                    [
                        'email' => $user->getEmail(),
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'avatar' => $user->getAvatar()
                    ]
                );
                $social = Social::where('name', $social)->first();
                $new_user->socials()->attach($social, ['token' => $user->token]);
                $this->auth->login($new_user);
            }
        } else {
            return $provider->redirect();
        }

        return response()->json(['message' => 'User successfuly logged in']);
    }

    /**
     * @return array
     */
    public function getAcceptsSocials()
    {
        return $this->accepts_socials;
    }
}
