<?php

namespace App\Http\Controllers;

use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Http\Requests\Following\FollowRequest;
use App\Social;
use App\Http\Requests\Following\FollowFacebookRequest;
use App\User;
use Log;

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
        $this->middleware('auth', ['only' => ['postFollow', 'postUnfollow', 'followFacebook']]);
        $this->middleware('privacy', ['only' => ['getFollowers', 'getFollowings']]);
    }

    /**
     * Follow specified user
     *
     * @param FollowRequest $request
     * @param \App\User $follow_user
     * @return \Illuminate\Http\JsonResponse
     */
    public function postFollow(FollowRequest $request, $follow_user)
    {
        /** @var User $user */
        /** @var User $follow_user */


        $user = $request->user();

        if ( !$user->followings()->find($follow_user->id) ) {

            $user->followings()->attach($follow_user->id);

        } else {
            return response()->json(['message' => 'You are already follow this user'], 403);
        }

        // broadcast an event
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
    public function postUnfollow(FollowRequest $request, $follow_user)
    {
        /**
         * @var \App\User $user
         */
        $user = $request->user();

        $following = $user->followings()->find($follow_user->id);
        if ($following) {
            $user->followings()->detach($follow_user->id);
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

    public function getFollowingsSocials($user) {
        return $user->followings;
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

    /**
     * Find facebook Users and follow
     * @param $request FollowFacebookRequest facebook ids of users
     * @param $social Social
     * @return mixed
     */
    public function followFacebook(FollowFacebookRequest $request) {
        Log::debug('followFacebook '.print_r($request->ids, 1));
        $selfUser = $request->user();
        // find facebook users with zoom accounts
        $users = Social::find(1)->users()->whereIn('social_user.social_key', $request->ids)->get();
        // follow these users
        Log::debug("self user id: {$selfUser->id}");
        $userFollowed = 0;
        foreach ($users as $u) {
            Log::debug("user id: {$u->id}");
            $followings = $selfUser->followings()->get();
            foreach ($followings as $f) {
                Log::debug("    I follow user with id {$f->id}");
            }
            if (!$request->user()->followings()->find($u->id)) {
                Log::debug("not following {$u->id} attach");
                $request->user()->followings()->attach($u);
                event(new UserFollowEvent($selfUser, $u));
                $userFollowed++;
            } else {
                Log::debug("already following {$u->id}");
            }
        }
        return response()->json(['message' => 'You are successfully followed users ' . $userFollowed]);
    }
}
