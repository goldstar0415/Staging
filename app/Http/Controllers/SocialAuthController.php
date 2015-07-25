<?php

namespace App\Http\Controllers;

use App\Social;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
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
            dd($user);
            $user = $provider->user();
            $exit_user = User::where('email', $user->getEmail())->first();

            if ($exit_user) {
//                return response()->
            } else {
                $new_user = new User(
                    [
                        'email' => $user->getEmail(),
                        'first_name' => $user->getNickname(),
                        'last_name' => $user->getName()
                    ]
                );
            }
        } else {
            return $provider->redirect();
        }
    }

    /**
     * @return array
     */
    public function getAcceptsSocials()
    {
        return $this->accepts_socials;
    }
}