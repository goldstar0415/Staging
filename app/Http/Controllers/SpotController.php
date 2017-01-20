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
            $spot->description = e($request->description);
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
                'restaurant',
                'hotel',
                'todo'
                ])
            ->append([
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

        $spot->description = $request->has('description') ? e($request->description) : '';

        if ($request->has('tags')) {
            $spot->tags = $request->input('tags');
        }
        $spot->locations = $request->input('locations');

        if (!$spot->is_private) {
            $spot->is_approved = false;
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
        $spotInfo     = $spot->getSpotExtension();
        $dates        = $request->all();
        $from         = date_parse_from_format( 'm.d.Y' , $dates['start_date'] );
        $to           = date_parse_from_format( 'm.d.Y' , $dates['end_date'] );
        
        $result['dates'] = [
            'from' => $from,
            'to'   => $to,
        ];
        
        $result['data']['hotels'] = false;
        $result['data']['booking'] = false;
        
        $fromString   = $from['year'] . '-' . (strlen($from['month']) == 1?'0':'') . $from['month'] . '-' . (strlen($from['day']) == 1?'0':'') . $from['day'];
        $startDate    = Carbon::create($from['year'], $from['month'], $from['day'], 0);
        $toString     =   $to['year'] . '-' . (strlen(  $to['month']) == 1?'0':'') .   $to['month'] . '-' . (strlen(  $to['day']) == 1?'0':'') .   $to['day'];
        $endDate      = Carbon::create($to['year'], $to['month'], $to['day'], 0);
        
        $result['data']['hotelsUrl'] = $spot->getHotelsUrl($spotInfo->hotelscom_url, $fromString, $toString);
        
        if($result['data']['hotelsUrl'])
        {
            $hotelsPageContent = $spot->getPageContent($result['data']['hotelsUrl']);
            
            if($hotelsPageContent)
            {
                $hotelPrice = $spot->getHotelsPrice($hotelsPageContent);
                $result['diff'] = $startDate->diffInDays($endDate);
                $result['data']['hotels'] = (!empty($hotelPrice))? '$'.($hotelPrice * $result['diff']):false;
            }
        }
        
        $result['data']['bookingUrl'] = $spot->getBookingUrl($spotInfo->booking_url, $fromString, $toString, true);
        if($result['data']['bookingUrl'])
        {
            $bookingPageContent = $spot->getPageContent($result['data']['bookingUrl'], [
                'headers' => $spot->getBookingHeaders()
            ]);
            if($bookingPageContent)
            {
                $result['data']['booking'] = '$' . ($spot->getBookingPrice($bookingPageContent));
            }
        }

        $result['result'] = $spot;
        return $result;
    }
    
    public function getRatingInfo($spot)
    {
        return $spot->getReviewsTotal();
    }
    
    public function getHours($spot)
    {
        $result = [];
        $spotInfo = $spot->getSpotExtension();
        if( empty($spotInfo->hours) )
        {
            $googlePlaceInfo = $spot->getGooglePlaceInfo();
            if(!empty($googlePlaceInfo))
            $result = $spot->saveGooglePlaceHours($googlePlaceInfo);
        }
        elseif(isset($spotInfo->hours) && !empty($spotInfo->hours))
        {
            $result = $spotInfo->hours;
        }
        return $result;
    }
    
    public function getBookingInfo($spot)
    {
        $result = [ 'photos' => [], 'amenities' => [] ];
        $spotInfo = $spot->getSpotExtension();
        $amenities = false;
        if($spotInfo)
        {
            $bookingUrl = $spot->getBookingUrl($spotInfo->booking_url);
            if(
                isset($spotInfo->booking_url) && 
                $spot->checkUrl($spotInfo->booking_url) && 
                $bookingUrl &&
                $bookingPageContent = $spot->getPageContent($bookingUrl, [
                    'headers' => $spot->getBookingHeaders()
                ])
            )
            {
                $result['photos'] = $spot->saveBookingPhotos($bookingPageContent);
                if( $amenities = $spot->saveBookingAmenities($bookingPageContent) )
                {
                    $result['amenities'] = $amenities;
                }
            }
        }
        return $result;
    }
    
    public function getCover($spot)
    {
        $result = [
            'cover_url' => null,
            ];
        
        $query = RemotePhoto::where('associated_type', Spot::class)
                ->where('associated_id', $spot->id)
                ->orderBy('image_type', 'desc')
                ->orderBy('created_at', 'asc');
        if( $query->exists() )
        {
            $result['cover_url'] = $query->first();
        }
        else
        {
            $spotInfo = $spot->getSpotExtension();
            $bookingUrl = (isset($spotInfo->booking_url))?$spot->getBookingUrl($spotInfo->booking_url):false;
            if(
                isset($spotInfo->booking_url) && 
                $spot->checkUrl($spotInfo->booking_url) && 
                $bookingUrl &&
                $bookingPageContent = $spot->getPageContent($bookingUrl, [
                    'headers' => $spot->getBookingHeaders()
                ])
            )
            {
                $result['cover_url'] = $spot->getBookingCover($bookingPageContent);
            }
        }
        return $result;
    }
    
    public function getCategoriesList() 
    {
        return SpotType::categoriesList();
    }
}
