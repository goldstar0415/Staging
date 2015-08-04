<?php

namespace App\Http\Controllers;

use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use Illuminate\Http\Request;

use App\Http\Requests;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['getFollow', 'getUnfollow']]);
    }

    public function getFollow(Request $request, $follow_user)
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

    public function getUnfollow(Request $request, $follow_user)
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

    public function getFollowers($user)
    {
        return $user->followers;
    }

    public function getFollowings($user)
    {
        return $user->followings;
    }
}
