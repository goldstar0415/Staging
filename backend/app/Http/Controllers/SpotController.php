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
use App\SpotView;
use App\User;
use App\RemotePhoto;
use App\SpotOwnerRequest as SpotOwnerRequestModel;
use App\Jobs\SpotViewUpdater;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Carbon\Carbon;
use Cache;
use Log;
use App\Http\Requests;
use App\Services\TextCleaner;

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
            'getFacebookPhotos',
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

        $this->parseRequestCover($request, $spot);
        $this->parseRequestDescription($request, $spot);

        if ($spot->is_private or $request->is_facebook_import) {
            $spot->is_approved = true;
        }
        if ($request->is_facebook_import) {
            $spot->is_private = false;
            $spot->category()->associate(SpotTypeCategory::whereName('FaceBook')->first());
        }

        $request->user()->spots()->save($spot);

        $this->parseRequestTags($request, $spot);
        $this->parseRequestLocations($request, $spot);
        $this->parseRequestFiles($request, $spot);

        if ($spot->is_approved) {
            event(new OnSpotCreate($spot));
        }
        
        $this->dispatch(new SpotViewUpdater($spot->id, 'save'));

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

        $this->parseRequestCover($request, $spot);
        $this->parseRequestDescription($request, $spot);
        $this->parseRequestTags($request, $spot);
        $this->parseRequestLocations($request, $spot);

        if (!$spot->is_private) {
            $is_approved = ($spot->user_id != null)?false:true;
            if (auth()->check() && auth()->user()->hasRole('admin')) {
                $is_approved = true;
            }
            $spot->is_approved = $is_approved;
        }

        $spot->save();

        $this->deleteSpotPhotos($request, $spot);
        $this->parseRequestFiles($request, $spot);

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
        
        $this->dispatch(new SpotViewUpdater($spot->id, 'update'));

        return $spot;
    }

    /**
     * Parse spot cover from request
     * @param Request $request
     * @param Spot $spot
     */
    private function parseRequestCover($request, $spot)
    {
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $spot->cover = $cover->getRealPath();
        }
    }

    /**
     * Parse spot description from request
     * @param Request $request
     * @param Spot $spot
     */
    private function parseRequestDescription($request, $spot)
    {
        if ($request->has('description')) {
            $description = nl2br(e($request->description));
            $spot->description = $description;
        }
    }

    /**
     * Attach photos from request to given spot
     * @param Request $request
     * @param Spot $spot
     */
    private function parseRequestFiles($request, $spot)
    {
        if ($request->has('files')) {
            foreach ($request->file('files') as $file) {
                $spot->photos()->create([
                    'photo' => $file
                ]);
            }
        }
    }

    /**
     * Parse spot tags from request
     * @param Request $request
     * @param Spot $spot
     */
    private function parseRequestTags($request, $spot)
    {
        if ($request->has('tags')) {
            $spot->tags = $request->input('tags');
        }
    }

    /**
     * Parse spot locations from request
     * @param Request $request
     * @param Spot $spot
     */
    private function parseRequestLocations($request, $spot)
    {
        $spot->locations = $request->input('locations');
    }

    /**
     * Remove requested spot photos
     * @param $request
     * @param $spot
     */
    private function deleteSpotPhotos($request, $spot)
    {
        $deletedFiles = $request->input('deleted_files');
        if (!empty($deletedFiles) and $spot->photos()->find($deletedFiles)->count() === count($deletedFiles)) {
            SpotPhoto::destroy($deletedFiles);
        }
    }

    /**
     * Remove the specified spot from storage.
     *
     * @param SpotDestroyRequest $request
     * @param Spot $spot
     * @return array
     */
    public function destroy(SpotDestroyRequest $request, $spot)
    {
        $this->dispatch(new SpotViewUpdater($spot->id, 'delete'));
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
        $url = sprintf('%s/%s/spot/%s/%s', config('app.frontend_url'), $spot->user_id ?: '0', $spot->id, $spot->slug);
        $description = $spot->description ? TextCleaner::removeTimestamps($spot->description) : '';
        $hasCover = isset($spot->cover_url['original']) && is_string($spot->cover_url['original']) && !preg_match('/\/missing.png/i', $spot->cover_url['original']);
        $coverUrl = $hasCover ? $spot->cover_url['original'] : (self::getDummyImageUrl($spot) ?: $spot->cover_url['original']);

        return view('opengraph')->with('og', $og
                ->title($spot->title)
                ->image($coverUrl)
                ->description($description)
                ->url($url)
        );
    }

    /**
     * Get dummy cover image url by spot type
     * URL: {endpoint} {type} {img_id}
     *  {https://s3.eu-central-1.amazonaws.com/zt-develop}/assets/img/placeholders/{event}/{101}.jpg
     * @param Spot $spot
     * @return string
     */
    protected static function getDummyImageUrl($spot): string
    {
        static $maxImgIds = [
            'food' => 32,
            'shelter' => 84,
            'event' => 100,
        ];

        $S3BaseUrl = env('S3_ENDPOINT');

        if ( !$S3BaseUrl ) {
            Log::critical(__METHOD__ . ' - Please configure ENV S3_ENDPOINT');
            return null;
        }

        if ( isset($spot->category->type->name) ) {
            $type = $spot->category->type->name;

            if ( !isset($maxImgIds[$type]) ) {
                return null;
            }

            $maxImgId = $maxImgIds[$type];
            $imgId = $spot->id % $maxImgId;

            return sprintf('%s/assets/img/placeholders/%s/%s.jpg', $S3BaseUrl, $type, $imgId);
        } else {
            return null;
        }
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
        $result = [
            'success' => true,
            'data' => [
                'hotels' => null,
                'booking' =>null
            ]
        ];
        $dates        = $request->all();
        if(isset($dates['start_date']) && isset($dates['end_date']))
        {
            $parsedStartDate = date_parse_from_format( 'm.d.Y' , $dates['start_date'] );
            $parsedEndDate   = date_parse_from_format( 'm.d.Y' , $dates['end_date'] );

            $checkinDate = Carbon::create($parsedStartDate['year'], $parsedStartDate['month'], $parsedStartDate['day'], 0);
            $checkoutDate = Carbon::create($parsedEndDate['year'], $parsedEndDate['month'], $parsedEndDate['day'], 0);
            $checkinDateString  = $checkinDate->toDateString();
            $checkoutDateString = $checkoutDate->toDateString();

            $result['data']['hotelsUrl'] = $spot->getHotelsUrl($spot->hotelscom_url, $checkinDateString, $checkoutDateString);
            $result['days'] = $checkinDate->diffInDays($checkoutDate);
            if($result['data']['hotelsUrl'])
            {
                $hotelsPageContent = $spot->getPageContent($result['data']['hotelsUrl']);

                if($hotelsPageContent)
                {
                    $hotelPrice = $spot->getHotelsPrice($hotelsPageContent);
                    $result['data']['hotels'] = (!empty($hotelPrice))? '$'.($hotelPrice * $result['days']):false;
                }
                $result['data']['hotelsUrl'] = "https://www.jdoqocy.com/click-" . env('JDOQOCY_ID') . "?url=" . $result['data']['hotelsUrl'];
            }
            $result['data']['bookingUrl'] = $spot->getBookingUrl($spot->booking_url, $checkinDateString, $checkoutDateString);
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
                $result['data']['bookingUrl'] = $result['data']['bookingUrl'] . "&aid=" . env("BOOKING_AID");
            }
        }
        else
        {
            $result['success'] = false;
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
    
    public function getHotelsRating(Spot $spot) {
        $result = [
            'success' => true,
            'hotelscom' => [
                'rating' => null,
                'reviews_count' => null
            ]
        ];
        if($spot->getHotelsReviewsPage())
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
        else {
            $result['success'] = false;
        }
        return $result;
    }
    
    public function getBookingRating(Spot $spot) {
        $result = [
            'success' => true,
            'booking' => [
                'rating' => null,
                'reviews_count' => null
            ]
        ];
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
            $result = $spot->getBookingTotals();
            if($result['success'])
            {
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
        $result = $spot->getYelpBizInfo();
        $spot->getYelpReviewsFromApi(true);
        if($result['success'] && 
                ($spot->yelp_rating != $result['yelp']['rating'] || 
                $spot->yelp_reviews_count != $result['yelp']['reviews_count']))
        {
            $spot->yelp_rating = $result['yelp']['rating'];
            $spot->yelp_reviews_count = $result['yelp']['reviews_count'];
            $spot->save();
        }
        return $result;
    }
    
    public function getTripadvisorRating(Spot $spot) {
        $result = [
            'success' => true,
            'tripadvisor' => [
                'rating' => null,
                'reviews_count' => null,
            ]
        ];
        if($spot->getTripadvisorReviewsPage())
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
        else
        {
            $result['success'] = false;
        }
        return $result;
    }
    
    public function getFacebookRating(Spot $spot) {
        $result = $spot->getFacebookRating();
        if($result['success'] && 
                ($spot->facebook_rating != $result['facebook']['rating'] || 
                $spot->facebook_reviews_count != $result['facebook']['reviews_count']))
        {
            $spot->facebook_rating = $result['facebook']['rating'];
            $spot->facebook_reviews_count = $result['facebook']['reviews_count'];
            $spot->save();
        }
        return $result;
    }
    
    public function getGoogleRating(Spot $spot) {

        $result = $spot->getGoogleReviewsInfo();
        $spot->getGoogleReviews(true);
        if($result['success'] && 
                ($spot->google_rating != $result['google']['rating'] || 
                $spot->google_reviews_count != $result['google']['reviews_count']))
        {
            $spot->google_rating = $result['google']['rating'];
            $spot->google_reviews_count = $result['google']['reviews_count'];
            $spot->save();
        }
        return $result;
    }
    
    public function saveRating(Request $request, Spot $spot)
    {
        $result = ['success' => true];
        if($request->has('avg_rating') && $request->has('total_reviews'))
        {
            $updArr = [
                'avg_rating' => $request->avg_rating,
                'total_reviews' => $request->total_reviews
            ];
            SpotView::where('id', $spot->id)->update($updArr);
            $spot->update($updArr);
        }
        else 
        {
            $result['success'] = false;
        }
        return $result;
    }
    
    public function getFacebookPhotos(Spot $spot) {
        $result = [
            'success' => false,
            'facebook_photos' => null
            ];
        if(!empty($spot->facebook_url))
        {
            $response = $spot->getFacebookPhotos();
            if($response['success'])
            {
                $result = $response;
            }
        }
        return $result;
    }
}
