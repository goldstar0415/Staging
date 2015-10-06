<?php

namespace App\Http\Controllers;

use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Http\Requests\Following\FollowRequest;
use Illuminate\Http\Request;
use App\Http\Requests;

/**
 * Class FollowController
 * @package App\Http\Controllers
 *
 * Following system controller
 */
class FollowController extends Controller
{
    /**
     * FollowController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['getFollow', 'getUnfollow']]);
        $this->middleware('privacy', ['only' => ['getFollowers', 'getFollowings']]);
    }

    /**
     * Follow specified user
     *
     * @param FollowRequest $request
     * @param \App\User $follow_user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFollow(FollowRequest $request, $follow_user)
    {
        /**
         * @var \App\User $user
         */
        $user = $request->user();
        if (!$user->followings()->find($follow_user->id)) {
            $user->followings()->attach($follow_user);
        } else {
            return response()->json(['message' => 'You are already follow this user'], 403);
        }

        event(new UserFollowEvent($user, $follow_user));

        return response()->json(['message' => 'You are successfuly follow user ' . $follow_user->first_name]);
    }

    /**
     * Unfollow specified user
     *
     * @param FollowRequest $request
     * @param \App\User $follow_user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnfollow(FollowRequest $request, $follow_user)
    {
        /**
         * @var \App\User $user
         */
        $user = $request->user();

        $following = $user->followings()->find($follow_user->id);
        if ($following) {
            $user->followings()->detach($follow_user);
        } else {
            return response()->json(['message' => 'You are doesn\'t follow this user'], 403);
        }

        event(new UserUnfollowEvent($user, $follow_user));

        return response()->json(['message' => 'You are successfuly unfollow user ' . $follow_user->first_name]);
    }

    /**
     * Get specified user followers
     *
     * @param \App\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFollowers($user)
    {
        return $user->followers;
    }

    /**
     * Get specified user followings
     *
     * @param \App\User $user
     * @return mixed
     */
    public function getFollowings($user)
    {
        return $user->followings;
    }
}
