<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\Comment;
use App\Http\Requests\PaginateRequest;
use App\Services\Attachments;

use App\Http\Requests;

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
     * Display a listing of the resource.
     * @param PaginateRequest $request
     * @param \App\Plan $plan
     * @return mixed
     */
    public function index(PaginateRequest $request, $plan)
    {
        $comments = $plan->comments();

        return $this->paginatealbe($request, $comments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CommentStoreRequest|Request $request
     * @param \App\Plan $plan
     * @return \App\Comment
     */
    public function store(CommentStoreRequest $request, $plan)
    {
        $comment = new Comment($request->except('attachments'));
        $comment->sender()->associate($request->user());

        $plan->comments()->save($comment);
        $this->attachments->make($comment);

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
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
