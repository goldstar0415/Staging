<?php

namespace App\Http\Controllers;

use App\Album;
use App\Http\Requests\Album\AlbumRequest;
use App\Http\Requests\Album\PhotoUpdateRequest;
use App\Http\Requests\Album\StoreRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\Privacy;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
     * @var Privacy
     */
    private $privacy;

    /**
     * AlbumController constructor.
     * @param Guard $auth
     * @param Privacy $privacy
     */
    public function __construct(Guard $auth, Privacy $privacy)
    {
        $this->auth = $auth;
        $this->middleware('auth', ['except' => ['index', 'show', 'showForUser']]);
        $this->middleware('privacy', ['only' => 'showForUser']);
        $this->privacy = $privacy;
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
        $album = $request->user()->albums()->save(new Album($request->input()));
        foreach ($request->file('files') as $file) {
            $album->photos()->create([
                'photo' => $file,
                'location' => $request->input('location'),
                'address' => $request->input('address')
            ]);
        }

        $album->user_id = $request->user()->id;

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
        $result = $this->paginatealbe($request, $user->albums());

        $collection = null;
        if (!$result instanceof Collection) {
            $collection = $result->getCollection();
        } else {
            $collection = $result;
        }
        $collection->each(function ($album, $key) use ($collection) {
            if ($album->is_private and !$this->privacy->hasPermission($album->user, Privacy::FOLLOWINGS) or
                !$album->is_private and !$this->privacy->hasPermission($album->user, $album->user->privacy_photo_map)) {
                    $collection->offsetUnset($key);
            }
        });

        return $result;
    }

    /**
     * Display the specified album.
     *
     * @param Request $request
     * @param  Album $album Specific album
     * @return Album
     */
    public function show(Request $request, $album)
    {
        $user = $request->user();

        if ($album->is_private and !$this->privacy->hasPermission($album->user, Privacy::FOLLOWINGS) or
            !$album->is_private and !$this->privacy->hasPermission($album->user, $album->user->privacy_photo_map)) {
            abort(403, 'Access denied');
        }

        return $album->append('count_photos');
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
