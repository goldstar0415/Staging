<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetAvatarRequest;
use App\Http\Requests\SettingsUpdateRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;
use Illuminate\Contracts\Hashing\Hasher;

class SettingsController extends Controller
{
    private $auth;

    public function __construct(Guard $auth)
    {
        $this->middleware('auth');
        $this->middleware('base64upload:avatar', ['only' => 'postSetavatar']);
        $this->auth = $auth;
    }

    /**
     * Update the specified resource in storage.
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
                $user->update(['email' => $params['email']]);
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
            case 'notifications':
                $user->update($params);
                break;
            default:
                abort(400);
                break;
        }

        return response()->json(['message' => 'Settings successfuly changed']);
    }

    public function postSetavatar(SetAvatarRequest $request)
    {
        $user = $request->user();
        $user->avatar = $request->file('avatar');
        $user->save();
        
        return $user;
    }
}
