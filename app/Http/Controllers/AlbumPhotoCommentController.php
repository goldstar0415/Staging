<?php

namespace App\Http\Controllers;

use App\AlbumPhoto;
use App\AlbumPhotoComment;
use App\Http\Requests\PhotoCommentsRequest;
use App\Http\Requests\PhotoCommentStoreRequest;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AlbumPhotoCommentController extends Controller
{

    private $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PhotoCommentStoreRequest|Request $request
     * @param \App\AlbumPhoto $photos
     * @return Response
     */
    public function store(PhotoCommentStoreRequest $request, $photos)
    {
        $comment = new AlbumPhotoComment($request->all());
        $comment->photo()->associate($photos);
        $comment->user()->associate($this->auth->user());
        $photos->comments()->save($comment);

        return response()->json(['message' => 'Comment was successfuly created']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PhotoCommentsRequest $request
     * @param AlbumPhoto $photos
     * @return Response
     * @internal param int $id
     */
    public function destroy(PhotoCommentsRequest $request, $photos)
    {
        $photos->delete();

        return response()->json(['message' => 'Comment was successfuly deleted']);
    }
}
