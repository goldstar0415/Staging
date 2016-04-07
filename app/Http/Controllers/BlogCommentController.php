<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Requests\Blog\BlogCommentRequest;
use App\Http\Requests\CommentsRequest;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\PaginateRequest;
use App\Services\Attachments;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

/**
 * Class BlogCommentController
 * @package App\Http\Controllers
 *
 * Blog comment resource controller
 */
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
     * Display a listing of the blog comments.
     * @param PaginateRequest $request
     * @param \App\Blog $blog
     */
    public function index(PaginateRequest $request, $blog)
    {
        return $this->paginatealbe($request, $blog->comments()->with('sender'));
    }

    /**
     * Store a newly created blog comment in storage.
     *
     * @param BlogCommentRequest $request
     * @param Attachments $attachments
     * @param \App\Blog $blog
     * @return Comment
     */
    public function store(BlogCommentRequest $request, Attachments $attachments, $blog)
    {
        $comment = new Comment($request->except('attachments'));
        $comment->sender()->associate($request->user());

        $blog->comments()->save($comment);
        $attachments->make($comment);

        return $comment;
    }

    /**
     * Remove the specified blog comment from storage.
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
