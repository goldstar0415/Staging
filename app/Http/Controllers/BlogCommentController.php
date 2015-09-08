<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\PaginateRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BlogCommentController extends Controller
{
    /**
     * BlogCommentController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'index']);
    }

    /**
     * Display a listing of the resource.
     * @param PaginateRequest $request
     * @param \App\Blog $blog
     */
    public function index(PaginateRequest $request, $blog)
    {
        return $this->paginatealbe($request, $blog->comments());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CommentStoreRequest $request
     * @param \App\Blog $blog
     * @return Comment
     */
    public function store(CommentStoreRequest $request, $blog)
    {
        $comment = new Comment($request->except('attachments'));
        $comment->sender()->associate($request->user());

        $blog->comments()->save($comment);

        return $comment;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CommentsRequest $request
     * @param \App\Blog $blog
     * @param \App\Comment $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CommentsRequest $request, $blog, $comment)
    {
        return response()->json(['result' => $comment->delete()]);
    }
}
