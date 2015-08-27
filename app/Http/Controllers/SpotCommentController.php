<?php

namespace App\Http\Controllers;

use App\Events\OnSpotComment;
use App\Events\OnSpotCommentDelete;
use App\Http\Requests\Spot\Comment\SpotCommentRequest;
use App\Http\Requests\Spot\Comment\SpotCommentStoreRequest;
use App\Spot;
use App\Comment;

use App\Http\Requests;

class SpotCommentController extends Controller
{
    /**
     * SpotCommentController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Spot $spot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index($spot)
    {
        return $spot->comments;
    }

    /**
     * Store a newly created resource in storage.
     * @param SpotCommentStoreRequest $request
     * @param Spot $spot
     * @return Comment
     */
    public function store(SpotCommentStoreRequest $request, $spot)
    {
        $comment = new Comment($request->all());
        $comment->user()->associate($request->user());

        $spot->comments()->save($comment);

        event(new OnSpotComment($comment));

        return $comment;
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
