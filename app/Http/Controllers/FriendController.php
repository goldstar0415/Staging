<?php

namespace App\Http\Controllers;

use App\Friend;
use App\Http\Requests\Friend\FriendRequest;
use App\Http\Requests\Friend\SetFriendAvatar;
use App\Http\Requests\Friend\StoreFriendRequest;
use App\Http\Requests\Friend\UpdateFriendRequest;

use App\Http\Requests;
use App\Http\Requests\PaginateRequest;

/**
 * Class FriendController
 * @package App\Http\Controllers
 *
 * Friend resource controller
 */
class FriendController extends Controller
{
    /**
     * FriendController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('base64upload:avatar', ['only' => 'setAvatar']);
    }

    /**
     * Display a listing of the friends.
     * @param PaginateRequest $request
     * @return mixed
     */
    public function index(PaginateRequest $request)
    {
        return $this->paginatealbe($request, $request->user()->friends());
    }

    /**
     * Store a newly created friend in storage.
     * @param StoreFriendRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFriendRequest $request)
    {
        $inputs = $request->all();

        $request->user()->friends()->save(new Friend($inputs));

        return response()->json(['message' => 'Friend successfuly added']);
    }

    /**
     * Display the specified friend.
     *
     * @param \App\Friend $friend
     * @return Friend
     */
    public function show($friend)
    {
        return $friend;
    }

    /**
     * Update the specified friend in storage.
     * @param UpdateFriendRequest $request
     * @param \App\Friend $friend
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateFriendRequest $request, $friend)
    {
        $inputs = $request->all();

        $friend->update($inputs);

        return response()->json(['message' => 'Friend successfuly updated']);
    }

    /**
     * Remove the specified friend from storage.
     * @param FriendRequest $request
     * @param \App\Friend $friend
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(FriendRequest $request, $friend)
    {
        $friend->delete();

        return response()->json(['message' => 'Friend successfuly deleted']);
    }

    /**
     * Set friend avatar
     *
     * @param SetFriendAvatar $request
     * @param \App\Friend $friend
     * @return Friend
     */
    public function setAvatar(SetFriendAvatar $request, $friend)
    {
        $friend->avatar = $request->file('avatar');
        $friend->save();

        return $friend;
    }
}
