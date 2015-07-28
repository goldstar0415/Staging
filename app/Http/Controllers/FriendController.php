<?php

namespace App\Http\Controllers;

use App\Friend;
use App\Http\Requests\Friend\FriendRequest;
use App\Http\Requests\Friend\StoreFriendRequest;
use App\Http\Requests\Friend\UpdateFriendRequest;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Phaza\LaravelPostgis\Geometries\Point;

class FriendController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return
     */
    public function index(Request $request)
    {
        return $request->user()->friends;
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreFriendRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreFriendRequest $request)
    {
        $inputs = $request->all();

        if (isset($inputs['location'])) {
            $inputs['location'] = new Point($inputs['location']['lat'], $inputs['location']['lng']);
        }

        $request->user()->friends()->save(new Friend($inputs));

        return response()->json(['message' => 'Friend successfuly added']);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Friend $friend
     * @return Friend
     */
    public function show($friend)
    {
        return $friend;
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateFriendRequest $request
     * @param \App\Friend $friend
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateFriendRequest $request, $friend)
    {
        $inputs = $request->all();

        if (isset($inputs['location'])) {
            $inputs['location'] = new Point($inputs['location']['lat'], $inputs['location']['lng']);
        }

        $friend->update($inputs);

        return response()->json(['message' => 'Friend successfuly updated']);
    }

    /**
     * Remove the specified resource from storage.
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
}
