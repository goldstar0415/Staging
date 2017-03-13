<?php

namespace App\Http\Controllers;

use App\AlbumPhoto;
use App\Http\Requests\AlbumPhoto\AlbumPhotoRequest;
use App\Http\Requests\AlbumPhoto\AlbumPhotoUpdateRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Http\Requests;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class AlbumPhotoController
 * @package App\Http\Controllers
 *
 * Album photo resource controller
 */
class AlbumPhotoController extends Controller
{
    /**
     * Display the specified album photo.
     *
     * @param AlbumPhoto $photos
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function show($photos)
    {
        return $photos->load('comments');
    }

    /**
     * Set the user avatar with the album photo
     *
     * @param Guard $auth
     * @param AlbumPhoto $photos
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAvatar(Guard $auth, AlbumPhoto $photos)
    {
        $auth->user()->avatar = $photos->photo->url();

        return response()->json(['message' => 'Avatar was successfuly changed']);
    }

    /**
     * Update the specified album photo in storage.
     *
     * @param  AlbumPhotoUpdateRequest $request
     * @param  AlbumPhoto $photos
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(AlbumPhotoUpdateRequest $request, $photos)
    {
        if ($request->has('location')) {
            $photos->address = $request->input('address');
            $lat = $request->input('location.lat');
            $lng = $request->input('location.lng');
            $photos->location = new Point($lat, $lng);
            $photos->save();
        }

        return response()->json(['message' => 'Photo was successfuly updated']);
    }

    /**
     * Remove the specified album photo from storage.
     *
     * @param AlbumPhotoRequest $request
     * @param AlbumPhoto $photos
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AlbumPhotoRequest $request, $photos)
    {
        $photos->delete();

        return response()->json(['message' => 'Photo was successfuly deleted']);
    }

    /**
     * Get all album photos with pagination
     *
     * @param PaginateRequest $request
     * @param \App\Album $album
     */
    public function photos(PaginateRequest $request, $album)
    {
        return $this->paginatealbe($request, $album->photos());
    }
	
	/**
	 * Get last uploaded photos for specified album for authorized User
	 * @param \App\Http\Controllers\Request $request
	 * @return type
	 */
	public function lastUploadedPhotos(Request $request, $album) {
		$photosIds = $request->session()->pull('lastPhotosSaved-album-'.$album->id, []);
		return $photos = count($photosIds) ? AlbumPhoto::whereIn('id', $photosIds)->get() : [];
	}
}
