<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use Illuminate\Contracts\Auth\Guard;

use App\Http\Requests;

/**
 * Class AlbumPhotoCommentController
 * @package App\Http\Controllers
 *
 * Album photo comment resource controller
 */
class AlbumPhotoCommentController extends Controller
{

    /**
     * @var Guard
     */
    private $auth;

    /**
     * AlbumPhotoCommentController constructor.
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Show photo comments
     *
     * @param \App\AlbumPhoto $photo
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index($photo)
    {
        $comments = $photo->comments;
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
     * Store a newly created album photo comment in storage.
     *
     * @param CommentStoreRequest $request
     * @param \App\AlbumPhoto $photo
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CommentStoreRequest $request, $photo)
    {
        $comment = new Comment($request->all());
        $comment->commentable()->associate($photo);
        $comment->sender()->associate($this->auth->user());
        $photo->comments()->save($comment);

        return $comment;
    }

    /**
     * Remove the specified album photo comment from storage.
     *
     * @param CommentsRequest $request
     * @param \App\AlbumPhoto $photo
     * @param Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CommentsRequest $request, $photo, $comment)
    {
        return response()->json(['result' => $comment->delete()]);
    }
}
