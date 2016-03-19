<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\Comment;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\Plan\PlanCommentStoreRequest;
use App\Services\Attachments;

use App\Http\Requests;

/**
 * Class PlanCommentController
 * @package App\Http\Controllers
 *
 * Plan comment resource controller
 */
class PlanCommentController extends Controller
{
    /**
     * @var Attachments
     */
    private $attachments;

    /**
     * PlanCommentController constructor.
     * @param Attachments $attachments
     */
    public function __construct(Attachments $attachments)
    {
        $this->middleware('auth', ['except' => 'index']);
        $this->attachments = $attachments;
    }

    /**
     * Display a listing of the plan comments.
     * @param PaginateRequest $request
     * @param \App\Plan $plan
     * @return mixed
     */
    public function index(PaginateRequest $request, $plan)
    {
        $comments = $plan->comments()->with('sender');

        return $this->paginatealbe($request, $comments);
    }

    /**
     * Store a newly created plan comment in storage.
     *
     * @param PlanCommentStoreRequest $request
     * @param \App\Plan $plan
     * @return \App\Comment
     */
    public function store(PlanCommentStoreRequest $request, $plan)
    {
        $comment = new Comment($request->except('attachments'));
        $comment->sender()->associate($request->user());

        $plan->comments()->save($comment);
        $this->attachments->make($comment);

        return $comment;
    }

    /**
     * Remove the specified plan comment from storage.
     *
     * @param CommentsRequest $request
     * @param \App\Plan $plan
     * @param \App\Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CommentsRequest $request, $plan, $comment)
    {
        return response()->json(['result' => $comment->delete()]);
    }
}
