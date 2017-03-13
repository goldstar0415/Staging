<?php

namespace App\Http\Controllers;

use App\Events\OnSpotComment;
use App\Events\OnSpotCommentDelete;
use App\Http\Requests\PaginateCommentRequest;
use App\Http\Requests\Spot\Comment\SpotCommentRequest;
use App\Http\Requests\Spot\Comment\SpotCommentStoreRequest;
use App\Services\Attachments;
use App\Spot;
use App\Comment;

use App\Http\Requests;

/**
 * Class SpotCommentController
 * @package App\Http\Controllers
 *
 * Spot comment resource controller
 */
class SpotCommentController extends Controller
{
    /**
     * @var Attachments
     */
    private $attachments;

    /**
     * SpotCommentController constructor.
     * @param Attachments $attachments
     */
    public function __construct(Attachments $attachments)
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->attachments = $attachments;
    }

    /**
     * Display a listing of the spot comments.
     *
     * @param PaginateCommentRequest $request
     * @param Spot $spot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(PaginateCommentRequest $request, $spot)
    {
        $comments = Comment::with('sender')
            ->where('commentable_id', $spot->id)->where('commentable_type', Spot::class);

        return $this->paginatealbe($request, $comments);
    }

    /**
     * Store a newly created spot comment in storage.
     * @param SpotCommentStoreRequest $request
     * @param Spot $spot
     * @return Comment
     */
    public function store(SpotCommentStoreRequest $request, $spot)
    {
        $comment = new Comment(['body' => $request->input('body')]);
        $comment->sender()->associate($request->user());

        $spot->comments()->save($comment);
        $this->attachments->make($comment);

        event(new OnSpotComment($comment));

        return $comment;
    }

    /**
     * Display the specified spot comment.
     *
     * @param Spot $spot
     * @param Comment $comment
     * @return Comment
     */
    public function show($spot, $comment)
    {
        return $comment;
    }

    /**
     * Update the specified spot comment in storage.
     *
     * @param SpotCommentRequest $request
     * @param Spot $spot
     * @param Comment $comment
     * @return Comment
     */
    public function update(SpotCommentRequest $request, $spot, $comment)
    {
        $comment->update($request->all());

        return $comment;
    }

    /**
     * Remove the specified spot comment from storage.
     *
     * @param Spot $spot
     * @param Comment $comment
     * @return array
     */
    public function destroy($spot, $comment)
    {
        event(new OnSpotCommentDelete($comment));

        return ['result' => $comment->delete()];
    }
}
