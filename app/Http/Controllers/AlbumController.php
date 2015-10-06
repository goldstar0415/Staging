<?php

namespace App\Http\Controllers;

use App\Album;
use App\Http\Requests\Album\AlbumRequest;
use App\Http\Requests\Album\PhotoUpdateRequest;
use App\Http\Requests\Album\StoreRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;

/**
 * Class AlbumController
 * @package App\Http\Controllers
 *
 * Album resource controller
 */
class AlbumController extends Controller
{

    /**
     * @var Guard
     */
    private $auth;

    /**
     * AlbumController constructor.
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
        $this->middleware('auth', ['except' => ['index', 'show', 'showForUser']]);
        $this->middleware('privacy', ['only' => 'showForUser']);
    }

    /**
     * Show authenticated user albums
     *
     * @return mixed
     */
    public function index()
    {
        return $this->auth->user()->albums;
    }

    /**
     * Store a newly created album in storage.
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        $params = $request->input();
        $album = new Album($params);
        $request->user()->albums()->save($album);
        foreach ($request->file('files') as $file) {
            $album->photos()->create([
                'photo' => $file,
                'location' => $request->input('location'),
                'address' => $request->input('address')
            ]);
        }

        return $album;
    }

    /**
     * Display the specified album for specific user.
     * @param PaginateRequest $request
     * @param \App\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function showForUser(PaginateRequest $request, $user)
    {
        return $this->paginatealbe($request, $user->albums());
    }

    /**
     * Display the specified album.
     *
     * @param  Album $album Specific album
     * @return Album
     */
    public function show($album)
    {
        return $album;
    }

    /**
     * Update the specified album in storage.
     *
     * @param  PhotoUpdateRequest $request
     * @param Album $albums Specific album
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
     * Remove the specified album from storage.
     *
     * @param AlbumRequest $request
     * @param Album $albums Specific album
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AlbumRequest $request, $albums)
    {
        $albums->delete();

        return response()->json(['message' => 'Album was successfuly deleted']);
    }
}
