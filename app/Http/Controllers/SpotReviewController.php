<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spot\Review\SpotReviewRequest;
use App\Http\Requests\Spot\Review\SpotReviewStoreRequest;
use App\Spot;
use App\SpotReview;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotReviewController extends Controller
{
    /**
     * SpotReviewController constructor.
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
        return $spot->reviews;
    }

    /**
     * Store a newly created resource in storage.
     * @param SpotReviewStoreRequest $request
     * @param Spot $spot
     * @return SpotReview
     */
    public function store(SpotReviewStoreRequest $request, $spot)
    {
        $review = new SpotReview($request->all());
        $review->spot()->associate($spot);
        $review->user()->associate($request->user());

        $review->save();

        return $review;
    }

    /**
     * Display the specified resource.
     *
     * @param Spot $spot
     * @param SpotReview $review
     * @return SpotReview
     */
    public function show($spot, $review)
    {
        return $review;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SpotReviewRequest|Request $request
     * @param Spot $spot
     * @param SpotReview $review
     * @return SpotReview
     */
    public function update(SpotReviewRequest $request, $spot, $review)
    {
        $review->update($request->all());

        return $review;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Spot $spot
     * @param SpotReview $review
     * @return array
     */
    public function destroy($spot, $review)
    {
        return ['result' => $review->delete()];
    }
}
