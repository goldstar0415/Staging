<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\PlanComment;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PlanCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param CommentsRequest $request
     * @param \App\Plan $plan
     * @return
     */
    public function index(CommentsRequest $request, $plan)
    {
        $comments = $plan->comments;
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
     * @param CommentStoreRequest|Request $request
     * @param \App\Plan $plan
     * @return \App\PlanComment
     */
    public function store(CommentStoreRequest $request, $plan)
    {
        $comment = new PlanComment($request->all());
        $comment->user()->associate($request->user());
        $plan->comments()->save($comment);

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
