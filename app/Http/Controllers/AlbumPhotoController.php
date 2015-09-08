<?php

namespace App\Http\Controllers;

use App\AlbumPhoto;
use App\Http\Requests\AlbumPhoto\AlbumPhotoRequest;
use App\Http\Requests\AlbumPhoto\AlbumPhotoUpdateRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;
use Phaza\LaravelPostgis\Geometries\Point;

class AlbumPhotoController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param AlbumPhoto $photos
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function show($photos)
    {
        return $photos->load('comments');
    }

    /**
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
     * @param PaginateRequest $request
     * @param \App\Album $album
     */
    public function photos(PaginateRequest $request, $album)
    {
        return $this->paginatealbe($request, $album->photos());
    }
}
