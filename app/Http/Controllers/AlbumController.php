<?php

namespace App\Http\Controllers;

use App\Album;
use App\Http\Requests\Album\AlbumRequest;
use App\Http\Requests\Album\PhotoUpdateRequest;
use App\Http\Requests\Album\StoreRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;

class AlbumController extends Controller
{

    private $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('auth', ['except' => ['index', 'show', 'showForUser']]);
        $this->middleware('privacy', ['only' => 'showForUser']);
    }

    public function index()
    {
        return $this->auth->user()->albums;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params = $request->input();
        $album = new Album($params);
        $this->auth->user()->albums()->save($album);
        foreach ($request->file('files') as $file) {
            $album->photos()->create([
                'photo' => $file
            ]);
        }

        return $album;
    }

    /**
     * Display the specified resource.
     * @param PaginateRequest $request
     * @param \App\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function showForUser(PaginateRequest $request, $user)
    {
        return $this->paginatealbe($request, $user->albums());
    }

    /**
     * Display the specified resource.
     *
     * @param  Album $album
     * @return Album
     */
    public function show($album)
    {
        return $album;
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
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $albums->photos()->create([
                    'photo' => $file
                ]);
            }
        }

        return $albums;
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
