<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\Comment;
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
        return $photo->comments->load('sender');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CommentStoreRequest $request
     * @param \App\Spot $spot
     * @param \App\SpotPhoto $photo
     * @return SpotPhoto
     */
    public function store(CommentStoreRequest $request, $spot, $photo)
    {
        $comment = new Comment($request->all());
        $comment->commentable()->associate($photo);
        $comment->sender()->associate($request->user());
        $photo->comments()->save($comment);

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CommentsRequest $request
     * @param \App\Spot $spot
     * @param SpotPhoto $photo
     * @param Comment $comment
     * @return array
     * @throws \Exception
     */
    public function destroy(CommentsRequest $request, $spot, $photo, $comment)
    {
        return ['result' => $comment->delete()];
    }
}
