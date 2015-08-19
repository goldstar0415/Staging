<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhotoComments\PhotoCommentsRequest;
use App\Http\Requests\PhotoComments\PhotoCommentStoreRequest;
use App\PhotoComment;
use App\SpotPhoto;

use App\Http\Requests;

class SpotPhotoCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param \App\Spot $spot
     * @param \App\SpotPhoto $photo
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index($spot, $photo)
    {
        $comments = $photo->comments;
        $comments->map(function ($comment) {
            /**
             * @var \App\PhotoComment $comment
             */
            $comment->addHidden('user_id');
            return $comment->load(['user' => function ($query) {
                $query->select(['id', 'first_name', 'last_name']);
            }]);
        });

        return $comments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PhotoCommentStoreRequest $request
     * @param \App\Spot $spot
     * @param \App\SpotPhoto $photo
     * @return SpotPhoto
     */
    public function store(PhotoCommentStoreRequest $request, $spot, $photo)
    {
        $comment = new PhotoComment($request->all());
        $comment->commentable()->associate($photo);
        $comment->user()->associate($request->user());
        $photo->comments()->save($comment);

        return $photo;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PhotoCommentsRequest $request
     * @param \App\Spot $spot
     * @param SpotPhoto $photo
     * @param PhotoComment $comment
     * @return array
     * @throws \Exception
     */
    public function destroy(PhotoCommentsRequest $request, $spot, $photo, $comment)
    {
        return ['result' => $comment->delete()];
    }
}
