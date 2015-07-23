<?php

namespace App\Http\Controllers;

use App\Album;
use App\AlbumPhoto;
use App\Http\Requests\Album\AlbumRequest;
use App\Http\Requests\Album\PhotoUpdateRequest;
use App\Http\Requests\Album\StoreRequest;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Validation\UnauthorizedException;
use Illuminate\Http\Request;

use App\Http\Requests;

class AlbumController extends Controller
{

    private $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('auth', ['only' => 'store']);
    }

    public function index()
    {
        return $this->auth->user()->albums();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $album = new Album($request->input());
        $this->auth->user()->albums()->save($album);
        foreach ($request->file('files') as $file) {
            $album->photos()->create([
                'photo' => $file
            ]);
        }
        return response()->json(['message' => 'Album was successfuly created']);
    }

    /**
     * Display the specified resource.
     * @param \App\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function showForUser($user)
    {
        return $user->albums;
    }

    /**
     * Display the specified resource.
     *
     * @param  Album $album
     * @return Album
     */
    public function show($album)
    {
        return $album->load('photos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PhotoUpdateRequest $request
     * @param Album $albums
     * @internal param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PhotoUpdateRequest $request, $albums)
    {
        $albums->update($request->only(['title', 'is_private']));
        foreach ($request->file('files') as $file) {
            $albums->photos()->create([
                'photo' => $file
            ]);
        }

        $deleted_ids = $request->input('deleted_ids');
        if ($albums->photos()->find($deleted_ids)->count() === count($deleted_ids)) {
            AlbumPhoto::destroy($deleted_ids);
        } else {
            throw new UnauthorizedException();
        }

        return response()->json(['message' => 'Album was successfuly created']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AlbumRequest $request
     * @param Album $albums
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AlbumRequest $request, $albums)
    {
        $albums->delete();

        return response()->json(['message' => 'Album was successfuly deleted']);
    }
}
