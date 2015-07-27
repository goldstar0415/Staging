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


    public function getFollow(Request $request, $id)
    {
        $follow_user = User::findOrFail($id);
        /**
         * @var \App\User $user
         */
        $user = $request->user();

        if (!$user->followings()->find($id)) {
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

    public function getUnfollow(Request $request, $id)
    {
        $follow_user = User::findOrFail($id);
        /**
         * @var \App\User $user
         */
        $user = $request->user();

        $following = $user->followings()->where('following_id', $id)->first();
        if ($following) {
            $following->delete();
        } else {
            return response()->json(['message' => 'You are doesn\'t follow this user'], 403);
        }

        event(new UserUnfollowEvent($user, $follow_user));

        return response()->json(['message' => 'You are successfuly unfollow user ' . $follow_user->first_name]);
    }

    public function getFollowers($id)
    {
        $user = User::findOrFail($id);

        return $user->followers->load('follower');
    }

    public function getFollowings($id)
    {
        $user = User::findOrFail($id);

        return $user->followings->load('following');
    }
}
