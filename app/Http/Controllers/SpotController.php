<?php

namespace App\Http\Controllers;

use App\ChatMessage;
use App\Events\OnMessage;
use App\Events\OnSpotCreate;
use App\Events\OnSpotUpdate;
use App\Http\Requests\Spot\SpotCategoriesRequest;
use App\Http\Requests\Spot\SpotDestroyRequest;
use App\Http\Requests\Spot\SpotFavoriteRequest;
use App\Http\Requests\Spot\SpotIndexRequest;
use App\Http\Requests\Spot\SpotInviteRequest;
use App\Http\Requests\Spot\SpotOwnerRequest;
use App\Http\Requests\Spot\Review\SpotReviewRequest;
use App\Http\Requests\Spot\SpotReportRequest;
use App\Http\Requests\Spot\SpotStoreRequest;
use App\Http\Requests\Spot\SpotUnFavoriteRequest;
use App\Http\Requests\Spot\SpotUpdateRequest;
use App\Http\Requests\SpotExportRequest;
use App\Services\Privacy;
use App\Spot;
use App\SpotPhoto;
use App\SpotReport;
use App\SpotType;
use App\SpotTypeCategory;
use App\SpotVote;
use App\User;
use App\RemotePhoto;
use App\SpotOwnerRequest as SpotOwnerRequestModel;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Carbon\Carbon;
use Cache;

use App\Http\Requests;

/**
 * Class SpotController
 * @package App\Http\Controllers
 *
 * Spot resource controller
 */
class SpotController extends Controller
{
    
    /**
     * @var Guard
     */
    private $auth;
    
    /**
     * SpotController constructor.
     */
    public function __construct(Guard $auth)
    {
        $this->middleware('auth', ['except' => [
            'index', 
            'show', 
            'categories', 
            'favorites', 
            'preview', 
            'export', 
            'getCover', 
            'getBookingInfo', 
            'prices', 
            'getHours',
            'getRatingInfo',
            'getGoogleRating',
            'getFacebookRating',
            'getTripadvisorRating',
            'getYelpRating',
            'getBookingRating',
            'getHotelsRating',
            'saveRating',
            ]
        ]);
        $this->middleware('base64upload:cover', ['only' => ['store', 'update']]);
        $this->middleware('privacy', ['except' => ['store', 'update', 'destroy']]);
        $this->auth = $auth;
    }

    /**
     * Display a listing of the spots.
     * @param SpotIndexRequest $request
     * @param Privacy $privacy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(SpotIndexRequest $request, Privacy $privacy)
    {
        $user = $request->user();
        $user_id = (int)$request->get(
            'user_id',
            $user ? $user->id : null
        );
        $spots = null;
        if ($user and $user_id === $user->id) {
            $spots = Spot::withRequested();
        } else {
            $spots = Spot::query();
        }
        $target = User::findOrFail($user_id);

        $spots = $spots->where('user_id', $user_id);
        if (!$user or $user->id !== $user_id and !$privacy->hasPermission($target, $target->privacy_events)) {
            $spots = $spots->where('is_private', false);
        }
        $spots = $spots->with('comments');

        return $this->paginatealbe($request, $spots);
    }

    /**
     * Store a newly created spot in storage.
     * @param SpotStoreRequest $request
     * @return Spot
     */
    public function store(SpotStoreRequest $request)
    {
        
        $spot = new Spot($request->except([
            'locations',
            'tags',
            'files',
            'cover',
            'description',
            'is_facebook_import'
        ]));

        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $spot->cover = $cover->getRealPath();
        }
        if ($spot->is_private or $request->is_facebook_import) {
            $spot->is_approved = true;
        }
        if ($request->is_facebook_import) {
            $spot->is_private = false;
            $spot->category()->associate(SpotTypeCategory::whereName('FaceBook')->first());
        }
        if ($request->has('description')) {
            $description = nl2br(e($request->description));
            $spot->description = $description;
        }

        $request->user()->spots()->save($spot);
        if ($request->has('tags')) {
            $spot->tags = $request->input('tags');
        }
        $spot->locations = $request->input('locations');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $spot->photos()->create([
                    'photo' => $file
                ]);
            }
        }

        if ($spot->is_approved) {
            event(new OnSpotCreate($spot));
        }

        return $spot->load('category.type');
    }

    /**
     * Display the specified spot.
     *
     * @param  Spot $spot
     * @return $this
     */
    public function show($spot)
    {
        $res = $spot
            ->load([
                'photos',
                'user',
                'tags',
                'comments',
                'remotePhotos',
                ])
            ->append([
                'reviews_count',
                'count_members',
                'members',
                'comments_photos',
                'auth_rate',
                'amenities',
                'slug'
                ]);
        //if(empty($spot->rating))
        //{
        //    $spot->reviews_total = Cache::get('spot-ratings-' . $spot->id);
        //}
        if (isset($res->remotePhotos)) {
            foreach($res->remotePhotos as $p) {
                if (isset($p->url)) {
                    $p->photo_url = [
                            'original'  => $p->url,
                            'medium'    => $p->url,
                            'thumb'     => $p->url
                    ];
                    $res->photos->push($p);
                }
            }
        }
        return $res;
    }

    /**
     * Update the specified spot in storage.
     *
     * @param  SpotUpdateRequest $request
     * @param  \App\Spot $spot
     * @return Spot
     */
    public function update(SpotUpdateRequest $request, $spot)
    {
        $spot->update($request->except([
            'description',
            'locations',
            'tags',
            'files',
            'cover',
            'deleted_files',
            '_method',
            'is_private'
        ]));
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $spot->cover = $cover->getRealPath();
        }
        
        if($request->has('description'))
        {
            $description = nl2br(e($request->description));
            $spot->description = $description;
        }
        if ($request->has('tags')) {
            $spot->tags = $request->input('tags');
        }
        $spot->locations = $request->input('locations');

        if (!$spot->is_private) {
            $is_approved = ($spot->user_id != null)?false:true;
            if(auth()->check() && auth()->user()->hasRole('admin'))
            {
                $is_approved = true;
            }
            $spot->is_approved = $is_approved;
        }

        $spot->save();

        $deleted_files = $request->input('deleted_files');

        if (!empty($deleted_files) and $spot->photos()->find($deleted_files)->count() === count($deleted_files)) {
            SpotPhoto::destroy($deleted_files);
        }

        if ($request->has('files')) {
            foreach ($request->file('files') as $file) {
                $spot->photos()->create([
                    'photo' => $file
                ]);
            }
        }

        if ($request->is_private != $spot->is_private) {
            if (!$request->is_private) {
                $spot->is_approved = false;
                $spot->is_private = false;
            } else {
                $spot->is_private = true;
                $spot->is_approved = true;
            }
            $spot->save();
        }

        if ($spot->is_approved) {
            event(new OnSpotUpdate($spot));
        }

        return $spot;
    }

    /**
     * Remove the specified spot from storage.
     *
     * @param SpotDestroyRequest $request
     * @param Spot $spot
     * @return bool|null
     */
    public function destroy(SpotDestroyRequest $request, $spot)
    {
        return ['result' => $spot->delete()];
    }

    /**
     * Get spots categories
     *
     * @param SpotCategoriesRequest $request
     * @return \Illuminate\Database\Eloquent\Collection|null|static[]
     */
    public function categories(SpotCategoriesRequest $request)
    {
        $type_categories = null;
        if ($request->has('type')) {
            $type_categories = SpotType::where('name', $request->get('type'))->with('categories')->first()->categories;
        } else {
            $type_categories = SpotType::with('categories')->get();
        }

        return $type_categories;
    }

    /**
     * Rate the spot
     *
     * @param SpotReviewRequest $request
     * @param \App\Spot $spot
     * @return SpotVote
     */
    public function rate(SpotReviewRequest $request, $spot)
    {
        $vote = new SpotVote($request->all());
        $vote->user()->associate($request->user());
        $spot->votes()->save($vote);

        return $vote;
    }
    
    /**
     * Add a review
     *
     * @param SpotReviewRequest $request
     * @param \App\Spot $spot
     * @return SpotVote
     */
    public function saveReview(SpotReviewRequest $request, $spot)
    {
        $vote = new SpotVote($request->all());
        $vote->user()->associate($request->user());
        $spot->votes()->save($vote);

        return $vote;
    }

    /**
     * Show favorites user spot
     *
     * @param Request $request
     * @return mixed
     */
    public function favorites(Request $request)
    {
        return User::find($request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))->favorites()->with('comments')->paginate((int)$request->get('limit', 10));
    }

    /**
     * Add the spot to favorites
     *
     * @param SpotFavoriteRequest $request
     * @param \App\Spot $spot
     * @return array
     */
    public function favorite(SpotFavoriteRequest $request, $spot)
    {
        $spot->favorites()->attach($request->user());

        return ['result' => true];
    }

    /**
     * Remove the spot from favorites
     *
     * @param SpotUnFavoriteRequest $request
     * @param \App\Spot $spot
     * @return array
     */
    public function unfavorite(SpotUnFavoriteRequest $request, $spot)
    {
        $spot->favorites()->detach($request->user());

        return ['result' => true];
    }

    /**
     * Invite the user to the spot
     *
     * @param SpotInviteRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function invite(SpotInviteRequest $request)
    {
        $user = $request->user();
        if (!Spot::whereId($request->spot_id)->exists()) {
            abort(403, 'The spot not found or not approved');
        }
        foreach ($request->input('users') as $user_id) {
            $message = new ChatMessage(['body' => '']);
            $user->chatMessagesSend()->save($message, ['receiver_id' => $user_id]);
            $message->spots()->attach((int) $request->input('spot_id'));

            event(new OnMessage($user, $message, User::find($user_id)->random_hash));
        }

        return response('Ok');
    }

    /**
     * The specified spot preview.
     * @param Spot $spot
     * @return Spot
     */
    public function preview($spot)
    {
        $og = new OpenGraph();

        return view('opengraph')->with(
            'og',
            $og->title($spot->title)
            ->image($spot->cover->url())
            ->description($spot->description)
            ->url(config('app.frontend_url') . '/user/' . $spot->user_id . '/spots/' . $spot->id)
        );
    }

    /**
     * Get the spot members
     *
     * @param Request $request
     * @param \App\Spot $spot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function members(Request $request, $spot)
    {
        return $spot->calendarUsers;
    }

    /**
     * Export the spot
     * @param Request $request
     * @param \App\Spot $spot
     * @return
     */
    public function export(Request $request, $spot)
    {
        return response()->ical($spot);
    }

    /**
     * Create owner spot request
     * @param SpotOwnerRequest $request
     * @param \App\Spot $spot
     * @return SpotOwnerRequestModel
     */
    public function ownerRequest(SpotOwnerRequest $request, $spot)
    {
        $owner_request = new SpotOwnerRequestModel($request->except('spot_id'));
        $owner_request->spot()->associate($spot);
        $owner_request->user()->associate($request->user());
        $owner_request->save();

        return $owner_request;
    }

    /**
     * Report the spot
     *
     * @param SpotReportRequest $request
     * @param \App\Spot $spot
     * @return SpotReport
     */
    public function report(SpotReportRequest $request, $spot)
    {
        $report = new SpotReport();

        switch((int)$request->reason) {
            case 0:
                $report->reason = SpotReport::WRONG;
                break;
            case 1:
                $report->reason = SpotReport::INAPPROPRIATE;
                break;
            case 2:
                $report->reason = SpotReport::DUPLICATE;
                break;
            case 3:
                $report->reason = SpotReport::SPAM;
                break;
            case 4:
                $report->reason = SpotReport::OTHER;
                break;
            default:
                abort(403, 'Forbidden');
                break;
        }

        $report->text = $request->text;
        $report->spot()->associate($spot);

        $report->save();

        return $report;
    }
    
    /**
     * Get booking.com and hotels.com prices
     *
     * @param Request $request
     * @param \App\Spot $spot
     * @return array
     */
    
    public function prices (Request $request, Spot $spot)
    {
        $result       = [];
        $dates        = $request->all();
        $from         = date_parse_from_format( 'm.d.Y' , $dates['start_date'] );
        $to           = date_parse_from_format( 'm.d.Y' , $dates['end_date'] );
        
        $result['data']['hotels'] = false;
        $result['data']['booking'] = false;
        
        $fromString   = $from['year'] . '-' . (strlen($from['month']) == 1?'0':'') . $from['month'] . '-' . (strlen($from['day']) == 1?'0':'') . $from['day'];
        $startDate    = Carbon::create($from['year'], $from['month'], $from['day'], 0);
        $toString     =   $to['year'] . '-' . (strlen(  $to['month']) == 1?'0':'') .   $to['month'] . '-' . (strlen(  $to['day']) == 1?'0':'') .   $to['day'];
        $endDate      = Carbon::create($to['year'], $to['month'], $to['day'], 0);
        
        $result['data']['hotelsUrl'] = $spot->getHotelsUrl($spot->hotelscom_url, $fromString, $toString);
        $result['days'] = $startDate->diffInDays($endDate);
        if($result['data']['hotelsUrl'])
        {
            $hotelsPageContent = $spot->getPageContent($result['data']['hotelsUrl']);
            
            if($hotelsPageContent)
            {
                $hotelPrice = $spot->getHotelsPrice($hotelsPageContent);
                $result['data']['hotels'] = (!empty($hotelPrice))? '$'.($hotelPrice * $result['days']):false;
            }
        }
        
        $result['data']['bookingUrl'] = $spot->getBookingUrl($spot->booking_url, $fromString, $toString, true);
        if($result['data']['bookingUrl'])
        {
            $bookingPageContent = $spot->getPageContent($result['data']['bookingUrl'], [
                'headers' => $spot->getBookingHeaders()
            ]);
            if($bookingPageContent)
            {
                $bookingPrice = $spot->getBookingPrice($bookingPageContent);
                $result['data']['booking'] = (!empty($bookingPrice))? '$'.$bookingPrice: false;
            }
        }
        return $result;
    }

    public function getHours($spot)
    {
        $result = [];
        if( empty($spot->hours) )
        {
            $googlePlaceInfo = $spot->getGooglePlaceInfo();
            if(!empty($googlePlaceInfo))
            $result = $spot->saveGooglePlaceHours($googlePlaceInfo);
        }
        else
        {
            $result = $spot->hours;
        }
        return $result;
    }
    
    public function getBookingInfo($spot)
    {
        $result = [ 'photos' => [], 'amenities' => [] ];
        $amenities = false;
        $bookingPageContent = $spot->getBookingPage();
        if($bookingPageContent)
        {
            $result['photos'] = $spot->saveBookingPhotos($bookingPageContent);
            if( $amenities = $spot->saveBookingAmenities($bookingPageContent) )
            {
                $result['amenities'] = $amenities;
            }
        }
        return $result;
    }
    
    public function getCover($spot)
    {
        $result = [
            'cover_url' => null,
            ];
        if (!empty($spot->cover_url))
        {
            $result['cover_url'] = $spot->cover_url;
        }
        else
        {
            $bookingPageContent = $spot->getBookingPage();
            if($bookingPageContent)
            {
                $bookingCoverObj = $spot->getBookingCover($bookingPageContent);
                if($bookingCoverObj)
                {
                    $url = $bookingCoverObj->url;
                    $result['cover_url'] = [
                        "original" => $url,
                        "medium" => $url,
                        "thumb" => $url
                    ];
                }
            }
        }
        return $result;
    }
    
    public function getCategoriesList() 
    {
        return SpotType::categoriesList();
    }
    
    // Ratings info and reviews methods
    
    public function getRatingInfo($spot)
    {
        return $spot->getReviewsTotal();
    }
    
    public function getHotelsRating(Spot $spot) {
        $result = [];
        if($spot->hotelscom_url)
        {
            $result['hotelscom']['rating'] = $spot->getHotelsRating();
            $result['hotelscom']['reviews_count'] = $spot->getHotelsReviewsCount();
            $spot->saveHotelsReviews();
            if( $spot->hotelscom_rating != $result['hotelscom']['rating'] || 
                $spot->hotelscom_reviews_count != $result['hotelscom']['reviews_count'])
            {
                $spot->hotelscom_rating = $result['hotelscom']['rating'];
                $spot->hotelscom_reviews_count = $result['hotelscom']['reviews_count'];
                $spot->save();
            }
        }
        return $result;
    }
    
    public function getBookingRating(Spot $spot) {
        $result = [];
        if(!empty($spot->booking_url))
        {
            $reviewsPageContent = null;
            $cachedBookingResponse = $spot->getCachedResponse('bookingReviewsPageContent');
            if($cachedBookingResponse)
            {
                $reviewsPageContent = $cachedBookingResponse;
            }
            if(!$cachedBookingResponse && $reviewsUrl = $spot->getBookingReviewsUrl($spot->booking_url))
            {
                $reviewsPageContent = $spot->getPageContent($reviewsUrl, [
                    'headers' => $spot->getBookingHeaders()
                ]);
                $spot->setCachedResponse('bookingReviewsPageContent', $reviewsPageContent);
            }
            if($reviewsPageContent)
            {
                $spot->getBookingReviews($reviewsPageContent, true);
            }
            $bookingTotals = $spot->getBookingTotals();
            if(!empty($bookingTotals))
            {
                $result['booking'] = $bookingTotals;
                if( $spot->booking_rating != $result['booking']['rating'] || 
                    $spot->booking_reviews_count != $result['booking']['reviews_count'])
                {
                    $spot->booking_rating = $result['booking']['rating'];
                    $spot->booking_reviews_count = $result['booking']['reviews_count'];
                    $spot->save();
                }
            }
        }
        return $result;
    }
    
    public function getYelpRating(Spot $spot) {
        $result = [];
        $yelpInfo = $spot->getYelpBizInfo();
        if( !empty($yelpInfo) )
        {
            $spot->getYelpReviewsFromApi(true);
            $result['yelp'] = $yelpInfo;
            if(!empty($result['yelp']) && 
                    ($spot->yelp_rating != $result['yelp']['rating'] || 
                    $spot->yelp_reviews_count != $result['yelp']['reviews_count']))
            {
                $spot->yelp_rating = $result['yelp']['rating'];
                $spot->yelp_reviews_count = $result['yelp']['reviews_count'];
                $spot->save();
            }
        }
        return $result;
    }
    
    public function getTripadvisorRating(Spot $spot) {
        $result = [];
        if($spot->tripadvisor_url)
        {
            $result['tripadvisor']['rating'] = $spot->getTripadvisorRating();
            $result['tripadvisor']['reviews_count'] = $spot->getTripadvisorReviewsCount();
            $spot->saveTripadvisorReviews();
            if( $spot->tripadvisor_rating != $result['tripadvisor']['rating'] || 
                $spot->tripadvisor_reviews_count != $result['tripadvisor']['reviews_count'])
            {
                $spot->tripadvisor_rating = $result['tripadvisor']['rating'];
                $spot->tripadvisor_reviews_count = $result['tripadvisor']['reviews_count'];
                $spot->save();
            }
        }
        return $result;
    }
    
    public function getFacebookRating(Spot $spot) {
        $result = [];
        if( $facebookRating = $spot->getFacebookRating())
        {
            $result['facebook'] = $facebookRating;
            if(!empty($result['facebook']) && 
                    ($spot->facebook_rating != $result['facebook']['rating'] || 
                    $spot->facebook_reviews_count != $result['facebook']['reviews_count']))
            {
                $spot->facebook_rating = $result['facebook']['rating'];
                $spot->facebook_reviews_count = $result['facebook']['reviews_count'];
                $spot->save();
            }
        }
        return $result;
    }
    
    public function getGoogleRating(Spot $spot) {
        $result = [];
        if(!empty($spot->getGooglePlaceInfo()))
        {
            $spot->getGoogleReviews(true);
            $result['google'] = $spot->getGoogleReviewsInfo();
            if(!empty($result['google']) && 
                    ($spot->google_rating != $result['google']['rating'] || 
                    $spot->google_reviews_count != $result['google']['reviews_count']))
            {
                $spot->google_rating = $result['google']['rating'];
                $spot->google_reviews_count = $result['google']['reviews_count'];
                $spot->save();
            }
        }
        return $result;
    }
    
    public function saveRating(Request $request, Spot $spot)
    {
        if($request->has('avg_rating') && $request->has('total_reviews'))
        {
            $spot->update([
                'avg_rating' => $request->avg_rating,
                'total_reviews' => $request->total_reviews
            ]);
        }
    }
    
    public function getFacebookPhotos(Spot $spot) {
        $result = ['facebook_photos' => null];
        if(!empty($spot->facebook_url))
        {
            $result['facebook_photos'] = $spot->getFacebookPhotos();
        }
        return $result;
    }
}
