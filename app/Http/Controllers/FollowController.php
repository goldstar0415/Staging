<?php

namespace App\Http\Controllers;

use App\Events\UserFollowEvent;
use App\Events\UserUnfollowEvent;
use App\Following;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

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

        if (!$user->followings()->where('following_id', $follow_user->id)->first()) {
            $following = new Following();
            $following->follower()->associate($user);
            $following->following()->associate($follow_user);
            $following->save();
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

        $following = $user->followings()->where('following_id', $follow_user->id)->first();
        if ($following) {
            $following->delete();
        } else {
            return response()->json(['message' => 'You are doesn\'t follow this user'], 403);
        }

        event(new UserUnfollowEvent($user, $follow_user));

        return response()->json(['message' => 'You are successfuly unfollow user ' . $follow_user->first_name]);
    }

    public function getFollowers($user)
    {
        return $user->followers->load('follower');
    }

    public function getFollowings($user)
    {
        return $user->followings->load('following');
    }
}
