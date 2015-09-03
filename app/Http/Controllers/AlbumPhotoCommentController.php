<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;

class AlbumPhotoCommentController extends Controller
{

    private $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Show photo comments
     *
     * @param \App\AlbumPhoto $photos
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index($photos)
    {
        $comments = $photos->comments;
        $comments->map(function ($comment) {
            /**
             * @var \App\Comment $comment
             */
            $comment->addHidden('user_id');
            return $comment->load('sender');
        });

        return $comments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CommentStoreRequest $request
     * @param \App\AlbumPhoto $photos
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CommentStoreRequest $request, $photos)
    {
        $comment = new Comment($request->all());
        $comment->commentable()->associate($photos);
        $comment->sender()->associate($this->auth->user());
        $photos->comments()->save($comment);

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CommentsRequest $request
     * @param \App\AlbumPhoto $photos
     * @param Comment $comment
     * @return \Illuminate\Http\JsonResponse
     * @internal param int $id
     */
    public function destroy(CommentsRequest $request, $photos, $comment)
    {
        return response()->json(['result' => $comment->delete()]);
    }
}
