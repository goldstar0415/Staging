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
        return $this->auth->user()->albums->each(function (Album $album) {
            $album->append('count_photos');
        });
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
		// check if Uploads album exists for a user
		if (!$user->albums()->where('title', 'Uploads')->first()) {
			$album				= new Album();
			$album->user_id		= $user->id;
			$album->title		= 'Uploads';
			$album->is_private	= true;
			$album->save();
		} else {
			//Log::debug('album exists');
		}
		
        $result = $this->paginatealbe($request, $user->albums()->where(function ($query) use ($user) {
            $query->where(function ($query) use ($user) {
                $query->where('is_private', false)
                    ->whereRaw($this->privacy->hasPermission($user, $user->privacy_photo_map) ? 'true' : 'false');
            })->orWhere(function ($query) use ($user) {
                $query->where('is_private', true)
                    ->whereRaw($this->privacy->hasPermission($user, Privacy::FOLLOWINGS) ? 'true' : 'false');
            });
        }));

        foreach ($result as $album) {
            $album->append('count_photos');
        }

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
        if ($album->is_private and !$this->privacy->hasPermission($album->user, Privacy::FOLLOWINGS) or
            !$album->is_private and !$this->privacy->hasPermission($album->user, $album->user->privacy_photo_map)) {
            abort(403, 'Access denied');
        }

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
		$photos = [];
        $albums->update($request->only(['title', 'is_private','location', 'address']));
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $f = $albums->photos()->create([
                    'photo' => $file
                ]);
				$photos[] = $f->id;
            }
        }
		$request->session()->put('lastPhotosSaved-album-'.$albums->id, $photos);
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
