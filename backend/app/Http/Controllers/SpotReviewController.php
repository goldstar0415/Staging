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
    protected $dummyAvatarUrlTemplate;

    /**
     * SpotReviewController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);

        $adorableConfig = config('services.adorable');
        $this->dummyAvatarUrlTemplate = $adorableConfig['avatarUrlTemplate'];
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
        $paginatedCollection = $this->paginatealbe($request, $query);

        // use dummy avatars instead of empty images
        if ( isset($paginatedCollection) && is_object($paginatedCollection) && method_exists($paginatedCollection, 'toArray') ) {
            $results = $paginatedCollection->toArray();
            if (isset($results['data'])) {
                foreach ($results['data'] as &$res) {
                    if ( isset($res['remote_user_avatar']) && preg_match('/\/missing\.[pngjeif]{3,4}$/i', $res['remote_user_avatar']) ) {
                        $identifier = isset($res['user_id']) ? $res['user_id'] : (isset($res['remote_id']) ? $res['remote_id'] : rand(1e3, 5e3));
                        $res['remote_user_avatar'] = $this->getDummyAvatarUrl(128, $identifier);
                    }
                }
            }

            return $results;
        }

        return $paginatedCollection;
    }

    protected function getDummyAvatarUrl($size, $identifier)
    {
        return str_replace([':size', ':identifier'], [$size, $identifier], $this->dummyAvatarUrlTemplate);
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
