<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsUpdateRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Hashing\Hasher;
use Phaza\LaravelPostgis\Geometries\Point;

class SettingsController extends Controller
{
    private $auth;

    public function __construct(Guard $auth)
    {
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
                if (!empty($params['location'])) {
                    $params['location'] = new Point($params['location']['lat'], $params['location']['lng']);
                }
                $user->update($params);
                break;
            case 'security':
                $user->update($params['email']);
                break;
            case 'password':
                if ($hash->check($params['current_password'], $user->password)) {
                    $user->password = $hash->make($params['password']);
                    $user->save();
                } else {
                    return response()->json(['message' => 'Incorrect password'], 422);
                }
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
}
