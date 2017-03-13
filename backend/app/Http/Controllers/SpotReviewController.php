<?php

namespace App\Http\Controllers;

use App\Events\OnSpotReview;
use App\Events\OnSpotReviewDelete;
use App\Http\Requests\PaginateReviewRequest;
use App\Http\Requests\Spot\Review\SpotReviewRequest;
use App\Http\Requests\Spot\Review\SpotReviewStoreRequest;
use App\Spot;
use App\SpotVote;

use App\Http\Requests;

use Log;

/**
 * Class SpotReviewController
 * @package App\Http\Controllers
 *
 * Spot review resource controller
 */
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
     * Display a listing of the spot reviews.
     *
     * @param PaginateReviewRequest $request
     * @param Spot $spot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(PaginateReviewRequest $request, $spot)
    {
        $auth = auth();
        $query = $spot->votes();
        if($auth->check()) {
            $query->where(function($query) use($auth){
                    $query->where('user_id', '!=', $auth->user()->id)
                    ->orWhereNull('user_id');
            });
        }
        $query->with('user');
        $query->orderBy('created_at', 'DESC');
        return $this->paginatealbe($request, $query);
    }

    /**
     * Store a newly created spot review in storage.
     * @param SpotReviewRequest $request
     * @param Spot $spot
     * @return SpotVote
     */
    public function store(SpotReviewRequest $request, $spot)
    {
        $review = new SpotVote([
            'message' => $request->input('message'),
            'vote'    => $request->input('vote')
        ]);
        $review->user()->associate($request->user());

        $spot->votes()->save($review);

        event(new OnSpotReview($review));
        
        $review->spot_rating = $spot->getRatingAttribute();
        
        return $review;
    }

    /**
     * Display the specified spot review.
     *
     * @param Spot $spot
     * @param SpotVote $review
     * @return SpotVote
     */
    public function show($spot, $review)
    {
        return $review;
    }

    /**
     * Update the specified spot review in storage.
     *
     * @param SpotReviewStoreRequest $request
     * @param Spot $spot
     * @param $review
     * @return SpotVote
     */
    public function update(SpotReviewStoreRequest $request, $spot, $review)
    {
        $review = SpotVote::find($review);
        $review->update($request->all());
        $review->spot_rating = $spot->getRatingAttribute();
        return $review;
    }

    /**
     * Remove the specified spot review from storage.
     *
     * @param Spot $spot
     * @param SpotVote $review
     * @return array
     */
    public function destroy($spot, $review)
    {
        $review = SpotVote::find($review);
        $result = false;
        if($review)
        {
            event(new OnSpotReviewDelete($review));
            $result = $review->delete();
        }
        return ['result' => $result, 'spot_rating' => $spot->getRatingAttribute()];
    }
}
