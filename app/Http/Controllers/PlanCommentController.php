<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\PlanComment;
use App\Services\Attachments;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

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
     * @param Request $request
     * @param \App\Plan $plan
     * @return mixed
     */
    public function index(Request $request, $plan)
    {
        $comments = $plan->comments;
        $comments->map(function ($comment) {
            /**
             * @var \App\Comment $comment
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
     * @param CommentStoreRequest|Request $request
     * @param \App\Plan $plan
     * @return \App\PlanComment
     */
    public function store(CommentStoreRequest $request, $plan)
    {
        $comment = new PlanComment(['body' => $request->input('message')]);
        $comment->user()->associate($request->user());
        $plan->comments()->save($comment);

        $this->attachments->make($comment);

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CommentsRequest $request
     * @param \App\Plan $plan
     * @param \App\PlanComment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CommentsRequest $request, $plan, $comment)
    {
        return response()->json(['result' => $comment->delete()]);
    }
}
