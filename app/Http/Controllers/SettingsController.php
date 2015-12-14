<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckAliasRequest;
use App\Http\Requests\SetAvatarRequest;
use App\Http\Requests\SettingsUpdateRequest;
use App\Services\EmailChange\EmailChangeBroker;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;
use Illuminate\Contracts\Hashing\Hasher;

/**
 * Class SettingsController
 * @package App\Http\Controllers
 *
 * User settings controller
 */
class SettingsController extends Controller
{
    /**
     * @var Guard
     */
    private $auth;

    /**
     * SettingsController constructor.
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->middleware('auth');
        $this->middleware('base64upload:avatar', ['only' => 'postSetavatar']);
        $this->auth = $auth;
    }

    /**
     * Update the user settings.
     * @param SettingsUpdateRequest $request
     * @param Hasher $hash
     * @return \Illuminate\Http\JsonResponse
     */
    public function putIndex(SettingsUpdateRequest $request, Hasher $hash)
    {
        $params = $request->input('params');
        /**
         * @var \App\User $user
         */
        $user = $this->auth->user();

        switch ($request->getType()) {
            case 'personal':
                $user->update($params);
                break;
            case 'security':
                /**
                 * @var EmailChangeBroker $emailBroker
                 */
                $emailBroker = app('auth.email');
                $emailBroker->sendChangeLink($user, $params['email'], function ($message) {
                    $message->subject('Email change');
                });
                break;
            case 'password':
                if ($user->is_registered) {
                    if (!$hash->check($params['current_password'], $user->password)) {
                        return response()->json(['message' => 'Incorrect password'], 422);
                    }
                }
                $user->password = $hash->make($params['password']);
                $user->save();
                break;
            case 'privacy':
            case 'socials':
            case 'notifications':
                $user->update($params);
                break;
            default:
                abort(400);
                break;
        }

        return response()->json(['message' => 'Settings successfuly changed']);
    }

    /**
     * Set the user avatar
     *
     * @param SetAvatarRequest $request
     * @return mixed
     */
    public function postSetavatar(SetAvatarRequest $request)
    {
        $user = $request->user();
        $user->avatar = $request->file('avatar');
        $user->save();
        
        return $user;
    }

    /**
     * Check for available user alias
     *
     * @param CheckAliasRequest $request
     * @return array
     */
    public function checkAlias(CheckAliasRequest $request)
    {
        return ['result' => true];
    }
}
