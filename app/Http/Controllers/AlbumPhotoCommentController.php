<?php

namespace App\Http\Controllers;

use App\PhotoComment;
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
     * @param CommentStoreRequest $request
     * @param \App\AlbumPhoto $photos
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CommentStoreRequest $request, $photos)
    {
        $comment = new PhotoComment($request->all());
        $comment->commentable()->associate($photos);
        $comment->user()->associate($this->auth->user());
        $photos->comments()->save($comment);

        return $comment->load('user');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CommentsRequest $request
     * @param \App\AlbumPhoto $photos
     * @param PhotoComment $comment
     * @return \Illuminate\Http\JsonResponse
     * @internal param int $id
     */
    public function destroy(CommentsRequest $request, $photos, $comment)
    {
        return response()->json(['result' => $comment->delete()]);
    }
}
