<?php

namespace App;

use App\Contracts\CalendarExportable;
use App\Contracts\Commentable;
use App\Extensions\Attachable;
use App\Extensions\StartEndDatesTrait;
use App\Scopes\ApprovedScopeTrait;
use App\Scopes\NewestScopeTrait;
use App\Services\SocialSharing;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Carbon\Carbon;
use DB;
use Cache;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Organizer;
use Request;
use Log;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;
use Facebook\Exceptions\FacebookSDKException;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Spot
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_type_category_id
 * @property string $title
 * @property string $description
 * @property array $web_sites
 * @property \Codesleeve\Stapler\Attachment $cover
 * @property array $videos
 * @property bool $is_approved
 * @property bool $is_private
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Relation properties
 * @property User $user
 * @property SpotTypeCategory $category
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $votes
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 * @property \Illuminate\Database\Eloquent\Collection $tags
 * @property \Illuminate\Database\Eloquent\Collection $plans
 * @property \Illuminate\Database\Eloquent\Collection $points
 * @property \Illuminate\Database\Eloquent\Collection $calendarUsers
 *
 * Mutators properties
 * @property float $rating
 * @property array $locations
 * @property string $type
 * @property \Illuminate\Database\Eloquent\Collection $comments_photos
 */
class Spot extends BaseModel implements StaplerableInterface, CalendarExportable, Commentable
{
    use StaplerTrait, StartEndDatesTrait, NewestScopeTrait, ApprovedScopeTrait, Attachable;

    protected $guarded = ['id', 'user_id'];

    protected $appends = [
        'rating',
        'auth_rate',
        'cover_url',
        'is_favorite',
        'is_saved',
        'is_rated',
        'share_links',
        'slug'
    ];

    protected $with = ['category.type', 'points'];

    protected $hidden = ['cover_file_name', 'cover_file_size', 'cover_content_type'];

    protected $casts = [
        'web_sites' => 'array',
        'videos' => 'array',
        'hours' => 'array',
    ];

    protected $dates = ['start_date', 'end_date'];

    public $exceptCacheAttributes = [
        'is_favorite',
        'is_saved',
        'is_rated'
    ];
    
    protected $googlePlacesInfo = null;
    protected $yelpInfo = null;
    protected $yelpToken = null;
    protected $bookingPage = null;
    protected $booking_reviews_url = null;
    protected $hotelsReviewsPage = null;
    protected $tripadvisorReviewsPage = null;
    
    public $cacheExpiresDate = null;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('cover', [
            'styles' => [
                'thumb' => [
                    'dimensions' => '100x100#',
                    'convert_options' => ['quality' => 100]
                ],
                'medium' => '180x180#'
            ]
        ]);
        
        $this->cacheExpiresDate = Carbon::now()->addDays(7);
        
        parent::__construct($attributes);
    }

    /**
     * Get urls of 3 cover sizes
     */
    public function getCoverUrlAttribute()
    {
        $covers = [];
        if( $this->cover->originalFilename())
        {
            $covers = $this->getPictureUrls('cover');
        }
        if ( !$covers && $rph = $this->remotePhotos()->orderBy('image_type', 'desc')->first() ) 
        {
            $url = $rph->url;
            $covers = [
                "original" => $url,
                "medium" => $url,
                "thumb" => $url
            ];
        }
        if(!$covers)
        {
            $covers = $this->getPictureUrls('cover');
        }
        //dd($covers);
        return $covers;
    }

    /**
     * Get spot rating
     *
     * @return float
     */
    public function getRatingAttribute()
    {
        return round((float)$this->votes()->whereNull('remote_id')->avg('vote'), 1);
    }
    
    /**
     * Get authenticated user rate
     *
     * @return mixed
     */
    public function getAuthRateAttribute()
    {
        return (auth()->check()) ? $this->votes()->where('user_id', auth()->user()->id)->with('user')->first():false;
    }
    
    /**
     * Get spot amenities
     *
     * @return query
     */
    public function amenities_objects()
    {
        return $this->hasMany(SpotAmenity::class);
    }
    
    public function getAmenitiesAttribute() 
    {
        $amenitiescCollection = $this->amenities_objects()->get();
        $array = [];
        if(!empty($amenitiescCollection))
        {
            foreach($amenitiescCollection as $a)
            {
                
                $array[$a->title][] = strip_tags($a->item, '<strong>');
            }
        }
        return $array;
    }
    
    /**
     * Check is spot favorite for authenticated user
     *
     * @return bool
     */
    public function getIsFavoriteAttribute()
    {
        if ($user = Request::user()) {
            return $user->favorites()->find($this->id) ? true : false;
        }

        return false;
    }
    
    /**
     * Get spot slug from title 
     *
     * @return string
     */
    public function getSlugAttribute()
    {
        return str_slug($this->title, "-");
    }

    /**
     * Check is spot saved to calendar for authenticated user
     *
     * @return bool
     */
    public function getIsSavedAttribute()
    {
        if ($user = Request::user()) {
            return $user->calendarSpots()->find($this->id) ? true : false;
        }

        return false;
    }

    /**
     * Check is the authenticated user appreciated the spot
     *
     * @return bool
     */
    public function getIsRatedAttribute()
    {
        if ($user = Request::user()) {
            return SpotVote::where('spot_id', '=', $this->id)->where('user_id', '=', $user->id)->first() !== null;
        }

        return false;
    }

    public function hasOwner()
    {
        return !is_null($this->user_id);
    }

    /**
     * Set the spot web sites
     *
     * @param array $value
     */
    public function setWebSitesAttribute(array $value)
    {
        $this->attributes['web_sites'] = json_encode($value);
    }
    
    /**
     * Set hours attribute
     *
     * @param array $value
     */
    public function setHoursAttribute(array $value)
    {
        $this->attributes['hours'] = json_encode($value);
    }

    /**
     * Set the spot videos
     *
     * @param array $value
     */
    public function setVideosAttribute(array $value)
    {
        $this->attributes['videos'] = json_encode($value);
    }

    /**
     * Get the spot locations
     */
    public function getLocationsAttribute()
    {
        return $this->points;
    }

    /**
     * Get the spot type
     *
     * @return mixed
     */
    public function getTypeAttribute()
    {
        return $this->category->type['name'];
    }

    /**
     * Set the spot tags
     *
     * @param array $value
     */
    public function setTagsAttribute(array $value)
    {
        $tags_ids = [];

        foreach ($value as $tag) {
            $tags_ids[] = Tag::firstOrCreate(['name' => $tag])->id;
        }
        $this->tags()->sync($tags_ids);
    }
    
    public function getReviewsCountAttribute()
    {
        return $this->votes()->whereNull('remote_id')->count();
    }

    /**
     * Set the spot locations
     *
     * @param array $value
     */
    public function setLocationsAttribute(array $value)
    {
        $this->points()->delete();
        foreach ($value as $location) {
            $point = new SpotPoint();
            $point->location = $location['location'];
            $point->address = $location['address'];
            $this->points()->save($point);
        }
    }

    /**
     * Get count members of the spot
     *
     * @return int
     */
    public function getCountMembersAttribute()
    {
        return $this->calendarUsers()->count();
    }

    /**
     * Get all photos from comments
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCommentsPhotosAttribute()
    {
        $comments_photos = collect();
        $this->comments->each(function ($comment) use (&$comments_photos) {
            $comments_photos = $comments_photos->merge($comment->albumPhotos);
        });

        return $comments_photos;
    }

    /**
     * Get the spot members
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMembersAttribute()
    {
        return $this->calendarUsers()
            ->orderBy(DB::raw(config('database.connections.' . config('database.default') . '.rand_func')))
            ->take(6)->get();
    }

    /**
     * Get the spot share links
     *
     * @return array
     */
    public function getShareLinksAttribute()
    {
        $url = frontend_url('api', 'spots', $this->id, 'preview');

        return [
            'facebook' => SocialSharing::facebook($url),
            'twitter' => SocialSharing::twitter($url),
            'google' => SocialSharing::google($url)
        ];
    }

    /**
     * Get the user that owns the spot
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The walls that belongs to the spot
     */
    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    /**
     * Get the votes for the spot
     */
    public function votes()
    {
        return $this->hasMany(SpotVote::class);
    }

    /**
     * Get all of the comments for the spot
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all users which mark as favorite the spot
     */
    public function favorites()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get the spot tags
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the spot category
     */
    public function category()
    {
        return $this->belongsTo(SpotTypeCategory::class);
    }

    /**
     * Get the points for the spot
     */
    public function points()
    {
        return $this->hasMany(SpotPoint::class);
    }

    /**
     * Get the photos for the spot
     */
    public function photos()
    {
        return $this->hasMany(SpotPhoto::class)->orderBy('created_at');
    }

    /**
     * Get remote photos
     */
    public function remotePhotos()
    {
        return $this->morphMany(RemotePhoto::class, 'associated')->orderBy('created_at');
    }

    /**
     * The plans that belongs to the spot
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class);
    }

    /**
     * The users which added to calendar the spot
     */
    public function calendarUsers()
    {
        return $this->belongsToMany(User::class, 'calendar_spots')->withTimestamps();
    }

    /**
     * Scope a query to get only upcoming spots.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComing($query)
    {
        return $query->whereRaw("date_part('day', \"start_date\") = date_part('day', CURRENT_DATE) + 1
             and date_part('month', \"start_date\") = date_part('month', CURRENT_DATE)
             and date_part('year', \"start_date\") = date_part('year', CURRENT_DATE)");
    }

    /**
     * {@inheritDoc}
     */
    public static function exportableEvents(User $user)
    {
        return $user->calendarSpots()->where(...self::exportableConditions())->get();
    }

    /**
     * {@inheritDoc}
     */
    public static function exportableConditions()
    {
        return [
            'start_date',
            '>=',
            \DB::raw('NOW()')
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function exportable(User $user)
    {
        $spots = self::exportableEvents($user);

        /**
         * @var \App\Spot $spot
         */
        foreach ($spots as $spot) {
            yield self::makeVEvent($spot, $user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        return self::makeVEvent($this, $this->user);
    }

    /**
     * @param self $spot
     * @param User|null $user
     * @return Event
     */
    protected static function makeVEvent(self $spot, $user = null)
    {
        $ics_event = new Event($spot->id);
        if ($spot->description) {
            $ics_event->setDescription($spot->description);
        }
        $ics_event->setDtStart($spot->start_date);
        $ics_event->setDtEnd($spot->end_date);
        if ($point = $spot->points()->first()) {
            $ics_event->setLocation($point->address);
        }
        if (!empty($spot->web_sites)) {
            $ics_event->setUrl($spot->web_sites[0]);
        }
        $ics_event->setUseUtc(false);
        if ($user) {
            $ics_event->setOrganizer(new Organizer($user->first_name . ' ' . $user->last_name, ['email' => $user->email]));
        }
        $ics_event->setCategories($spot->category->display_name);
        $ics_event->setSummary($spot->title);

        return $ics_event;
    }

    /**
     * {@inheritDoc}
     */
    public function commentResourceOwnerId()
    {
        return $this->user_id;
    }

    /**
     * Scope a query to search by blog text.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $filter)
    {
        return $query
            ->where(DB::raw('title'), 'ilike', "%$filter%");
    }

    /**
     * Relations which needs to flush from cache
     * @return array
     */
    public function flushRelations()
    {
        return [
            'tags',
            'favorites',
            'calendarUsers'
        ];
    }
    
    /*
     * Hotels.com parser 
     */
    public function getHotelsUrl($url, $checkinDate = false, $checkoutDate = false)
    {
        if($this->checkUrl($url))
        {
            $hotelsQuery = [
                'pos' => 'HCOM_US',
                'locale' => 'en_US',
                'q-room-0-adults' => 1,
                'q-room-0-children' => 0,
                'tab' => 'description'
            ];
            if($checkinDate)
            {
                $hotelsQuery['q-check-in']  = $checkinDate;
            }
            if($checkoutDate)
            {
                $hotelsQuery['q-check-out'] = $checkoutDate;
            }
            return $url . '?' . http_build_query($hotelsQuery);
        }
        return false;
    }
    
    public function getHotelsPrice($hotelsRes)
    {
        $regexp = '/[^\-\d]*(\-?\d*).*/';
        if( $hotelsPriceObj = $hotelsRes->find('span.current-price', 0))
        {
            return (int)preg_replace($regexp,'$1',($hotelsPriceObj->innertext()));
        }
        elseif( $hotelsPriceObj = $hotelsRes->find('meta[itemprop=priceRange]', 0) )
        {
            $hotelsPrice = explode(' ' , $hotelsPriceObj->getAttribute('content'));
            return (int)preg_replace($regexp,'$1', (array_pop($hotelsPrice)));
        }
        return false;
    }
    
    public function getHotelsReviewsPage() 
    {
        if(!empty($this->hotelsReviewsPage))
        {
            return $this->hotelsReviewsPage;
        }
        if($cachedResponse = $this->getCachedResponse('hotelsReviewsPage'))
        {
            return $this->hotelsReviewsPage = $cachedResponse;
        }
        $url = $this->getHotelsReviewsUrl();
        if($url)
        {
            $hotelsPageContent = $this->getPageContent($url, []);
            if($hotelsPageContent)
            {
                $this->setCachedResponse('hotelsReviewsPage', $hotelsPageContent);
                return $this->hotelsReviewsPage = $hotelsPageContent;
            }
        }
        return $this->hotelsReviewsPage;
    }
    
    public function saveHotelsReviews()
    {
        $result = null;
        $page = $this->getHotelsReviewsPage();
        if($page)
        {
            $reviews = [];
            foreach( $page->find('.review-card') as $reviewObj )
            {
                $message = $reviewObj->find('.review-content .expandable-content', 0)->innertext();
                if(empty($message))
                {
                    continue;
                }
                $rating = $reviewObj->find('.rating strong', 0)->innertext();
                if(empty($rating))
                {
                    continue;
                }
                $remote_id = 'hc_' . md5($message);
                if($this->votes()->where('remote_id', $remote_id)->exists())
                {
                    continue;
                }
                $remote_user = $reviewObj->find('.review-card-meta-reviewer', 0);
                $remote_user->find('.reviewer-data',0)->outertext = "";
                $date = Carbon::createFromTimestamp(round($reviewObj->getAttribute('data-review-date')/1000))->toDateTimeString();
                $reviews[] = [
                    'vote' => round(floatval($rating)),
                    'message' => $message,
                    'remote_id' => $remote_id,
                    'remote_type' => SpotVote::TYPE_HOTELS,
                    'remote_user_name' => SpotVote::remoteReviewerNameCheck(trim($remote_user->innertext())),
                    'created_at' => $date,
                    'updated_at' => $date,
                    'spot_id' => $this->id,
                ];
            }
            $this->votes()->insert($reviews);
            $result = $reviews;
        }
        return $result;
    }
    
    public function getHotelsReviewsUrl()
    {
        if(!empty($this->hotelscom_url) && $this->checkUrl($this->hotelscom_url))
        {
            $urlParts = explode('/', $this->hotelscom_url);
            $urlParts[3] = $urlParts[3] . '-tr';
            return implode('/', $urlParts);
        }
        return null;
    }
    
    public function getHotelsReviewsCount()
    {
        $result = null;
        $page = $this->getHotelsReviewsPage();
        if($page)
        {
            $element = $page->find('.filters .tt-all span', 0);
            if($element)
            {
                $result = intval(str_replace(['(', ')'], [''], trim($element->innertext())));
            }
        }
        return $result;
    }
    
    public function getHotelsRating() 
    {
        $result = null;
        $page = $this->getHotelsReviewsPage();
        if($page)
        {
            $element = $page->find('.overall strong', 0);
            if($element)
            {
                $result = floatval(str_replace(',','.', trim($element->innertext())));
            }
        }
        return $result;
    }
    
    /*
     * Booking.com parser 
     */
    
    public function getBookingUrl($url, $checkinDate = false, $checkoutDate = false)
    {
        if($this->checkUrl($url))
        {
            $bookingQuery  = [
                'room1'             => 'A',
                'selected_currency' => 'USD',
                'changed_currency'  => 1,
                'top_currency'      => 1, 
                'lang'              => 'en-us'
            ];
            if($checkinDate)
            {
                $bookingQuery['checkin'] = $checkinDate;
            }
            if($checkoutDate)
            {
                $bookingQuery['checkout'] = $checkoutDate;
            }
            $query = '?' . http_build_query($bookingQuery);
            return preg_replace( '#\..?.?.?.?.?\.?html#' , '.html' , $url) . $query;
        }
        return false;
    }
    
    public function getBookingHeaders()
    {
        return [
            'Host' => 'www.booking.com',
            'Connection' => 'keep-alive',
            'Cache-Control' => 'max-age=0',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, sdch',
            'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
            'Cookie' => 'lastSeen=0',
        ];
    }
    
    public function getBookingPrice($bookingRes)
    {
        $price = false;
        foreach($bookingRes->find('.rooms-table-room-price') as $bookingPriceObj)
        {
            $newPrice = trim(str_replace('US$', '', $bookingPriceObj->innertext()));
            $newPrice = str_replace(',', '', $newPrice);
            if( ($price && ( (int)$newPrice < (int)$price )) || !$price )
            {
                $price = $newPrice;
            }
        }
        $result = $price;
        if( $price )
        {
            $result = $price;
        }
        if( empty($result) && ($bookingPriceObj = $bookingRes->find('meta[itemprop=priceRange]', 0)) )
        {
            $result = explode(' ' , $bookingPriceObj->getAttribute('content'));
            $result = str_replace('US', '', array_pop($result));
        }
        return (int)preg_replace('/[^\-\d]*(\-?\d*).*/','$1',($result));
    }
    
    public function getBookingPage()
    {
        if(!empty($this->bookingPage))
        {
            return $this->bookingPage;
        }
        if($cachedResponse = $this->getCachedResponse('bookingPage'))
        {
            return $this->bookingPage = $cachedResponse;
        }
        if(!empty($this->booking_url))
        {
            $url = $this->getBookingUrl($this->booking_url);
            if($url)
            {
                $bookingPageContent = $this->getPageContent($url, [
                    'headers' => $this->getBookingHeaders()
                ]);
                if($bookingPageContent)
                {
                    $this->setCachedResponse('bookingPage', $bookingPageContent);
                    $this->bookingPage = $bookingPageContent;
                }
            }
        }
        return $this->bookingPage;
    }
    
    public function saveBookingPhotos($bookingRes)
    {
        $result = [];
        $needCover = true;
        if(RemotePhoto::where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->where('image_type', 1)
                        ->exists())
        {
            $needCover = false;
        }
        $urlsArr = [];
        if( $bookingSlider = $bookingRes->find('.hp-gallery-slides', 0) )
        {
            foreach( $bookingSlider->find('img') as $picture )
            {
                $url = $picture->getAttribute('src');
                if(empty($url))
                {
                    $url = $picture->getAttribute('data-lazy');
                }
                $filename = $this->getFilenameFromUrl($url);
                if( $filename && !RemotePhoto::where('url', 'like' , "%$filename%")
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists() )
                {
                    $urlsArr[] = $url;
                }
            }
        }
        if( $bookingSlider = $bookingRes->find('.bh-photo-grid', 0) )
        {
            foreach( $bookingSlider->find('a.active-image') as $picture )
            {
                $url = $picture->getAttribute('href');
                $filename = $this->getFilenameFromUrl($url);
                if( $filename && !RemotePhoto::where('url', 'like' , "%$filename%")
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists() )
                {
                    $urlsArr[] = $url;
                }
            }
        }
        if(!empty($urlsArr))
        {
            foreach($urlsArr as $url)
            {
                $imageType = 0;
                if($needCover)
                {
                    $imageType = 1;
                    $needCover = false;
                }
                $result[] = new RemotePhoto([
                    'url' => $url,
                    'image_type' => $imageType,
                    'size' => 'original',
                ]);
            }
        }
        if(!empty($result))
        {
            $this->remotePhotos()->saveMany( $result );
        }
        return collect($result)->sortBy('created_at');
    }
    public function getFilenameFromUrl($url)
    {
        if($this->checkUrl($url))
        {
            $path = parse_url($url, PHP_URL_PATH);
            $pathArr = explode('/', $path);
            $filename = array_pop($pathArr);
            return $filename;
        }
        return false;
    }
    
    public function saveBookingAmenities($bookingRes)
    {
        $result = [];
        if( ( $bookingAmenities = $bookingRes->find('div.facilitiesChecklist', 0) ) && $this->amenities_objects()->count() == 0 )
        {
            foreach( $bookingAmenities->find('.facilitiesChecklistSection') as $facilitiesChecklistSection )
            {
                $sectionTitle = $facilitiesChecklistSection->find('h5', 0);
                $facilityGroupIcon = $sectionTitle->find('.facilityGroupIcon', 0);
                if($facilityGroupIcon)
                {
                    $facilityGroupIcon->outertext = '';
                }
                $sectionTitle = trim($sectionTitle->innertext());
                foreach($facilitiesChecklistSection->find('li') as $sectionItem)
                {
                    $body = trim($sectionItem->innertext());
                    if( !SpotAmenity::where('spot_id', $this->id)
                                     ->where('title', $sectionTitle)
                                     ->where('item', $body)->exists() )
                    {
                        $amenity = new SpotAmenity([
                            'title' => $sectionTitle, 
                            'item' => $body,
                        ]);
                        $this->amenities_objects()->save($amenity);
                    }
                    $result[$sectionTitle][] = $body;
                }
            }
        }
        return $result;
    }


    public function getBookingReviewsUrl($booking_url)
    {
        if($this->checkUrl($booking_url))
        {
            $reviewsUrlArr  = explode( '/' , $booking_url);
            $reviewsUrl     = preg_replace( '#\..?.?.?.?.?\.?html#' , '' , end( $reviewsUrlArr ));
            $cc1            = $reviewsUrlArr[count($reviewsUrlArr) - 2];
            $url = 'http://www.booking.com/reviewlist.html?pagename=' . $reviewsUrl . '&cc1=' . $cc1 . '&rows=100&lang=en';
            $this->booking_reviews_url = $url;
            return $this->booking_reviews_url;
        }
        return false;
    }
    
    public function getBookingTotals()
    {
        $result = [
            'success' => true,
            'booking' => [
                'rating' => null,
                'reviews_count' => null
            ]
        ];
        $pageContent = $this->getBookingPage();
        if($pageContent)
        {
            $ratingObj = $pageContent->find('#review_list_main_score', 0);
            if(!empty($ratingObj))
            {
                $ratingvalue = round(((float) str_replace(',', '.', trim($ratingObj->innertext())))/2, 1);
                $result['booking']['rating'] = $ratingvalue;
            }
            $countObj = $pageContent->find('#review_list_score_count strong', 0);
            if(!empty($countObj))
            {
                $countValue = intval($countObj->innertext());
                $result['booking']['reviews_count'] = $countValue;
            }
        }
        else 
        {
            $result['success'] = false;
        }
        return $result;
    }
    
    public function getBookingReviews($reviewsContent, $save = false)
    {
        $result = null;
        $reviewsToSave = [];
        foreach( $reviewsContent->find('.review_item') as $reviewObj )
        {
            // Checking if there's no object with review text end going to next if it's true
            // And if review id doesn't exist going to next
            $reviewTextObj = $reviewObj->find('.review_item_review_content', 0);
            $idObj = $reviewObj->find('input[name=review_url]', 0);
            if(!$reviewTextObj || !$idObj) 
            {
                continue;
            }
            // Cleaning up text if we have what to clean
            foreach($reviewTextObj->find('.review_item_icon') as $icon)
            {
                $icon->outertext = "";
            }
            if( $noCommentsObj = $reviewTextObj->find('.review_none', 0))
            {
                $noCommentsObj->outertext = "";
            }
            // Checking is there something after cleaning and going to next if no
            if(empty(trim($reviewTextObj->innertext())))
            {
                continue;
            }
            $messArr = [];
            // Review may consist of two parts, negative and positive
            if($neg = $reviewTextObj->find('.review_neg', 0))
            {
                $messArr['neg'] = preg_replace('/(\[\/?strong\])/', '',trim($neg->innertext()));
            }
            if($pos = $reviewTextObj->find('.review_pos', 0))
            {
                $messArr['pos'] = preg_replace('/(\[\/?strong\])/', '',trim($pos->innertext()));
            }
            // If there's no positive and negative parts just pasting content
            if(empty($messArr))
            {
                $message =$reviewTextObj->innertext();
            }
            // Else gum up message from positive and negative parts
            else
            {
                $message =  ( isset($messArr['pos'])?'Positive: ' . (preg_replace('/\.$/', '', $messArr['pos']))  . '. ' : ''  ) .
                            ( isset($messArr['neg'])?'Negative: ' . $messArr['neg'] : '' );
            }
            // Cleaning up from [strong] tags
            $cleanedMessage =  preg_replace('/(\[\/?strong\])/', '', $message);
            $dateObj = $reviewObj->find('.review_item_date', 0);
            $date = ($dateObj) ? (new Carbon(trim($dateObj->innertext())))->toDateTimeString() : date("Y-m-d H:i:s");
            $item = new SpotVote();
            $item->remote_id = 'bk_' . $idObj->getAttribute( 'value' );
            $item->message = html_entity_decode($cleanedMessage);
            $scoreObj = $reviewObj->find('.review_item_review_score', 0);
            $item->vote = round((float)$scoreObj->innertext()/2);
            $item->remote_user_name = SpotVote::remoteReviewerNameCheck(trim($reviewObj->find('h4', 0)->innertext));
            $item->remote_user_avatar = str_replace('height=64&width=64', 'height=300&width=300', $reviewObj->find('.avatar-mask', 0)->getAttribute('src'));
            $item->created_at = $date;
            $item->updated_at = $date;
            $item->remote_type = SpotVote::TYPE_BOOKING;
            if( $save && !SpotVote::where('remote_id', $item->remote_id)->exists())
            {
                $reviewsToSave[] = $item;
            }
            $result[] = $item;
        }
        if(!empty($reviewsToSave))
        {
            $this->votes()->saveMany($reviewsToSave);
        }
        return $result;
    }
    
    public function getBookingCover($bookingRes)
    {
        $result = null;
        $resultUrl = null;
        if( $bookingSlider = $bookingRes->find('.hp-gallery-slides', 0) )
        {
            foreach( $bookingSlider->find('img') as $picture )
            {
                $url = $picture->getAttribute('src');
                if(empty($url))
                {
                    $url = $picture->getAttribute('data-lazy');
                }
                $filename = $this->getFilenameFromUrl($url);
                if( $filename && !RemotePhoto::where('url', 'like' , "%$filename%")
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists() )
                {
                    $resultUrl = $url;
                    break;
                }
            }
        }
        if( empty($resultUrl) && $bookingSlider = $bookingRes->find('.bh-photo-grid', 0) )
        {
            foreach( $bookingSlider->find('a.active-image') as $picture )
            {
                $url = $picture->getAttribute('href');
                $filename = $this->getFilenameFromUrl($url);
                if( $filename && !RemotePhoto::where('url', 'like' , "%$filename%")
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists() )
                {
                    $resultUrl = $url;
                    break;
                }
            }
        }
        if(!empty($resultUrl))
        {
            $result = new RemotePhoto([
                'url' => $resultUrl,
                'image_type' => 1,
                'size' => 'original',
            ]);
            $this->remotePhotos()->save( $result );
        }

        return $result;
    }
    
    /*
     * Google places methods 
     */
    
    public function getGooglePid()
    {
        $googlePid = (!empty($this->google_id)
                && $this->google_id != 'null'
                && $this->google_id != '0')?$this->google_id:false;
        
        return $googlePid;
    }
    
    public function getGooglePlaceInfo()
    {
        if($this->googlePlacesInfo)
        {
            $this->googlePlacesInfo;
        }
        if($cachedResponse = $this->getCachedResponse('googlePlacesInfo'))
        {
            return $this->googlePlacesInfo = $cachedResponse;
        }
        $googlePid = $this->getGooglePid();
        if($googlePid)
        {
            try
            {
                $url = config('services.places.baseUri') . config('services.places.placeUri') . '?placeid=' . $googlePid . '&key=' . config('services.places.api_key');
                $response = $this->getPageContent($url, [], true);
                if($response['status'] == "OK")
                {
                    $this->setCachedResponse('googlePlacesInfo', $response['result']);
                    $this->googlePlacesInfo = $response['result'];
                }
            }
            catch(Exception $e) {
                return false;
            }
        }
        return $this->googlePlacesInfo;
    }
    
    public function getGoogleReviewsInfo()
    {
        $googlePlaceInfo = $this->getGooglePlaceInfo();
        $result = [
            'success' => true,
            'google' => [
                'rating' => null,
                'reviews_count' => null,
            ]
        ];
        if($googlePlaceInfo && !empty($googlePlaceInfo['rating']) && !empty($googlePlaceInfo['reviews']))
        {
            $result['google'] = [
                'rating' => $googlePlaceInfo['rating'],
                'reviews_count' => count($googlePlaceInfo['reviews'])
            ];
        }
        else
        {
            $result['success'] = false;
        }
        return $result;
    }
    
    public function getGoogleReviews( $save = false )
    {
        $googlePlaceInfo = $this->getGooglePlaceInfo();
        $result = null;
        if( !empty($googlePlaceInfo['reviews']))
        {
            $googleReviews = $googlePlaceInfo['reviews'];
            $result = [];
            foreach($googleReviews as $item)
            {
                $remote_id = $this->getHashForGoogle($item);
                $itemObj = new SpotVote();
                $itemObj->remote_id = $remote_id;
                $itemObj->message = html_entity_decode($item['text']);
                $itemObj->vote = $item['rating'];
                $itemObj->created_at = date("Y-m-d H:i:s", $item['time']);
                $itemObj->remote_type = SpotVote::TYPE_GOOGLE;
                $itemObj->remote_user_name = SpotVote::remoteReviewerNameCheck($item['author_name']);
                $itemObj->remote_user_avatar = (!empty($item['profile_photo_url']))?$item['profile_photo_url']:'';
                if( $save && !SpotVote::where('remote_id', $remote_id)->where('spot_id', $this->id)->exists())
                {
                    $this->votes()->save($itemObj);
                }
                $result[] = $itemObj;
            }
        }
        return $result;
    }
    
    public function getGooglePlacePhotos( $save = false)
    {
        $googlePlaceInfo = $this->getGooglePlaceInfo();
        $result = [];
        $resToSave = [];
        if( !empty($googlePlaceInfo['photos']))
        {
            $googlePhotos = $googlePlaceInfo['photos'];
            foreach($googlePhotos as $item)
            {
                $photoUrl = $this->getGooglePhotoUrl($item['photo_reference']);
                
                $item = new RemotePhoto([
                        'url' => $photoUrl,
                        'image_type' => 0,
                        'size' => 'original',
                    ]);
                $result[] = $item;
                if( $save && !RemotePhoto::where('url', $photoUrl)
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists() )
                {
                    $resToSave[] = $item;
                }
            }
            if( $save )
            {
                $this->remotePhotos()->saveMany( $resToSave );
            }
            
        }
        return collect($result);
    }
    
    public function saveGooglePlaceHours($googlePlaceInfo)
    {
        $result = [];
        $googlePlaceInfo = $this->getGooglePlaceInfo();
        if( !empty($googlePlaceInfo['opening_hours']))
        {
            $openingHours = $googlePlaceInfo['opening_hours'];
            unset($openingHours['open_now']);
            $this->hours = $openingHours;
            $this->save();
            $result = $openingHours;
        }
        return $result;
    }
    
    protected function getGooglePhotoUrl($photo_reference)
    {
        return config('services.places.baseUri') . 'photo'
        . '?maxwidth=400'
        . '&photoreference=' . $photo_reference
        . '&key=' . config('services.places.api_key');
    }

    protected function getHashForGoogle($item)
    {
        $userAlias = '';
        if(isset($item['author_url']))
        {
            $url_path = parse_url($item['author_url'], PHP_URL_PATH);
            $url_path_arr = array_filter(explode('/', $url_path));
            foreach ($url_path_arr as $elem)
            {
                if (is_numeric($elem))
                {
                    $userAlias = $elem;
                    break;
                }
            }
        }
        return 'gg_' . $userAlias . $item['time'];
    }
    
    /*
     * Facebook Graph methods
     */
    
    public function getFacebookIdFromUrl($url)
    {
        $url = preg_replace(['/(.*)facebook\.com\//', '/\\?\/?\?(.*)/', '/(\/|\\))$/'], '', trim($url));
        $url = explode('/', $url);
        $id = false;
        if( count($url) > 1 )
        {
            $id = array_pop($url);
        }
        elseif( count($url) == 1)
        {
            $url = explode('-', $url[0]);
            if( count($url) > 1 )
            {
                $id = array_pop($url);
            }
            else
            {
                $id = $url[0];
            }
        }
        return (!empty($id))?$id:false;
    }
    
    public function getFacebookRating()
    {
        $result = [
            'success' => true,
            'facebook' => [
                'reviews_count' => null,
                'rating'        => null,
            ]
        ];
        if($cachedResponse = $this->getCachedResponse('facebookRating'))
        {
            return $cachedResponse;
        }
        if( $this->checkUrl($this->facebook_url))
        {
            $id = $this->getFacebookIdFromUrl($this->facebook_url);
            if($id)
            {
                try
                {
                    $fb = app(LaravelFacebookSdk::class);
                    $response = $fb->get('/' . $id . '?fields=rating_count,overall_star_rating,name', config('laravel-facebook-sdk.app_token'));
                    $values = $response->getGraphNode()->asArray();
                    $result['facebook']['reviews_count'] = $values['rating_count'];
                    $result['facebook']['rating']        = $values['overall_star_rating'];
                    $this->setCachedResponse('facebookRating', $result);
                    return $result;
                }
                catch (Exception $e) {
                    $result['success'] = false;
                }
                catch (FacebookSDKException $e) {
                    $result['success'] = false;
                }
            }
        }
        return $result;
    }
    
    public function getFacebookPhotos() {
        $result = [
            'success' => true,
            'facebook_photos' => null
        ];
        if($cachedResponse = $this->getCachedResponse('facebookPhotos'))
        {
            return $cachedResponse;
        }
        if( $this->checkUrl($this->facebook_url))
        {
            $id = $this->getFacebookIdFromUrl($this->facebook_url);
            if($id)
            {
                try
                {
                    $fb = app(LaravelFacebookSdk::class);
                    $response = $fb->get('/' . $id . '?fields=photos.limit(10){images}', config('laravel-facebook-sdk.app_token'));
                    $values = $response->getGraphNode()->asArray();
                    $images = [];
                    $needCover = ($this->remotePhotos()->where('image_type', 1)->exists()) ? false : true;
                    if(isset($values['photos']))
                    {
                        foreach($values['photos'] as $photo)
                        {
                            if(isset($photo['images']))
                            {
                                foreach($photo['images'] as $image)
                                {
                                    if($image['width'] <= 720 && !$this->remotePhotos()->where('url', $image['source'])->exists())
                                    {
                                        $images[] = new RemotePhoto([
                                            'url' => $image['source'],
                                            'image_type' => $needCover ? 1 : 0,
                                            'size' => 'original',
                                        ]);
                                        if($needCover)
                                        {
                                            $needCover = false;
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    if(!empty($images))
                    {
                        $this->remotePhotos()->saveMany($images);
                    }
                    $result['facebook_photos'] = $images;
                    $this->setCachedResponse('facebookPhotos', $result);
                    return $result;
                }
                catch (Exception $e) {
                    $result['success'] = false;
                }
                catch (FacebookSDKException $e) {
                    $result['success'] = false;
                }
            }
        }
        return $result;
    }
    
    /*
     * Yelp API methods 
     */
    
    public function getYelpToken()
    {
        if(!empty($this->yelpToken))
        {
            return $this->yelpToken;
        }
        if($cachedResult = $this->getCachedResponse('yelpToken'))
        {
            return $this->yelpToken = $cachedResult;
        }
        $client = new Client();
        $body = new PostBody();
        $body->forceMultipartUpload(true);
        $body->replaceFields([
                    'client_id' => config('services.yelp.client_id'),
                    'client_secret' => config('services.yelp.client_secret'),
                    'grant_type' => 'client_credentials'
                ]);
        $options = ['body' => $body];
        try
        {
            $response = $client->post(config('services.yelp.tokenUri'), $options);
            $result = json_decode($response->getBody()->getContents(), true)['access_token'];
            $this->setCachedResponse('yelpToken', $result);
            $this->yelpToken =  $result;
        }
        catch(Exception $e) { 
            return false;
        }
        return $this->yelpToken;
    }
    
    public function getYelpIdFromUrl($url)
    {
        if( $this->checkUrl($url) )
        {
            return str_replace('https://www.yelp.com/biz/', '', $url);
        }
        return false;
    }
    
    public function getYelpBizInfo()
    {
        $result = [
            'success' => true,
            'yelp' => [
                'reviews_count' => null,
                'rating'        => null
            ]
        ];
        if(!empty($this->yelp_url) || !empty($this->yelp_id))
        {
            $token = $this->getYelpToken();
            $id    = (!empty($this->yelp_id) && $this->yelp_id != 'null') ? $this->yelp_id : $this->getYelpIdFromUrl($this->yelp_url);
            if($token && $id)
            {
                $client = new Client();
                $headers = ['Authorization' => 'Bearer ' . $token];
                $url = 'https://api.yelp.com/v3/businesses/' . $id;
                try
                {
                    if($cachedResponse = $this->getCachedResponse('yelpBizInfo'))
                    {
                        $responseArray = $cachedResponse;
                    }
                    else
                    {
                        $response = $client->get($url , [
                            'headers' => $headers
                        ]);
                        $responseArray = json_decode($response->getBody()->getContents(), true);
                        $this->setCachedResponse('yelpBizInfo', json_decode($response->getBody()->getContents(), true));
                    }
                    if( !empty($responseArray['review_count']) && !empty($responseArray['rating']))
                    {
                        $result['yelp'] = [
                            'reviews_count' => $responseArray['review_count'],
                            'rating'        => $responseArray['rating']
                        ];
                    }
                }
                catch(RequestException $e) { 
                    $result['success'] = false; 
                }
            }
            
        }
        return $result;
    }
    
    public function getYelpReviewsFromApi($save = false)
    {
        $result = null;
        if(!empty($this->yelp_url))
        {
            $token = $this->getYelpToken();
            $id    = $this->getYelpIdFromUrl($this->yelp_url);
            if($token && $id)
            {
                $client = new Client();
                $headers = ['Authorization' => 'Bearer ' . $token];
                $url = 'https://api.yelp.com/v3/businesses/' . $id;
                try
                {
                    if($cachedResponse = $this->getCachedResponse('yelpReviewsFromApi'))
                    {
                        $yelp_reviews = $cachedResponse;
                    }
                    else
                    {
                        $response = $client->get($url . '/reviews', [
                            'headers' => $headers
                        ]);
                        $yelp_reviews = json_decode($response->getBody()->getContents(), true);
                        $this->setCachedResponse('yelpReviewsFromApi', $yelp_reviews);
                    }
                    if( !empty($yelp_reviews['reviews']))
                    {
                        $result = [];
                        foreach($yelp_reviews['reviews'] as $review)
                        {
                            $url_query = parse_url($review['url'], PHP_URL_QUERY);
                            parse_str($url_query, $url_query_parsed);
                            $remote_id = 'yp_' . $url_query_parsed['hrid'];
                            $item = new SpotVote();
                            $item->vote = $review['rating'];
                            $item->created_at = $review['time_created'];
                            $item->message = html_entity_decode($review['text']);
                            $item->remote_id = $remote_id;
                            $item->remote_type = SpotVote::TYPE_YELP;
                            $item->remote_user_name = SpotVote::remoteReviewerNameCheck($review['user']['name']);
                            $item->remote_user_avatar = $review['user']['image_url'];
                            if( $save && !SpotVote::where('remote_id', $remote_id)->exists())
                            {
                                $this->votes()->save($item);
                            }
                            $result[] = $item;
                        }
                    }
                }
                catch(RequestException $e) {
                    $result = false;
                }
            }
        }
        return $result;
    }
    
    public function getYelpReviewsFromPage($save = false)
    {
        $result = [];
        if($this->checkUrl($this->yelp_url))
        {
            $url = $this->yelp_url;
            $headers = $this->getYelpHeaders($url);
            $client = new Client();
            try
            {
                if($cachedResponse = $this->getCachedResponse('yelpReviewsFromPage'))
                {
                    $response = $cachedResponse;
                }
                else
                {
                    $response = $client->get($url . '/review_feed/', [
                        'headers' => $headers
                    ]);
                    $this->setCachedResponse('yelpReviewsFromPage', $response);
                }
                $responseBody = json_decode($response->getBody()->getContents(), true);
                if(!empty($responseBody['review_list']))
                {
                    $htmlString =   preg_replace(
                        '/([\n\r]+)/', 
                        '', 
                        html_entity_decode($responseBody['review_list'])
                    );
                    $html = new \Htmldom( $htmlString );
                    $reviews = $html->find('.review--with-sidebar');
                    $isFirst = true;
                    $result = [];
                    foreach($reviews as $reviewObj)
                    {
                        if($isFirst)
                        {
                            $isFirst = false;
                            continue;
                        }
                        $content = $reviewObj->find('div.review-content', 0);
                        $sidebar = $reviewObj->find('div.review-sidebar', 0);
                        $dateObj = $content->find('span.rating-qualifier', 0);
                        $dateInnerToDelete = $dateObj->find('small', 0);
                        $remoteId = 'yp_' . $reviewObj->getAttribute('data-review-id');
                        if(!empty($dateInnerToDelete))
                        {
                            $dateInnerToDelete->outertext = "";
                        }
                        $item = new SpotVote();
                        $item->vote = floatval($content->find('div.biz-rating .i-stars', 0)->getAttribute('title'));
                        $item->created_at = trim(strtotime($dateObj->innertext));
                        $item->message = html_entity_decode($content->find('p', 0)->innertext);
                        $item->remote_id = $remoteId;
                        $item->remote_type = SpotVote::TYPE_YELP;
                        $item->remote_user_name = SpotVote::remoteReviewerNameCheck(trim($sidebar->find('.user-display-name', 0)->innertext));
                        $item->remote_user_avatar = str_replace('60s.', '300s.', $sidebar->find('.photo-box img', 0)->src);
                        if( $save && !SpotVote::where('remote_id', $remoteId)->exists())
                        {
                            $this->votes()->save($item);
                        }
                        $result[] = $item;
                    }
                }
            }
            catch(RequestException $e) 
            {
                $result = false;
            }
        }
        return $result;
    }
    
    protected function getYelpHeaders($url) {
        return [
            ":authority" => "www.yelp.com",
            ":method" => "GET",
            ":path" => str_replace('https://www.yelp.com', '', $url) . "/review_feed/",
            ":scheme" => ":https",
            "accept" => "*/*",
            "accept-encoding" => "gzip, deflate, sdch, br",
            "accept-language" => "ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4",
            "referer" => $url,
            "user-agent" => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36",
            "x-distil-ajax" => "edysyssqrztsafvrr",
            "x-requested-with" => "XMLHttpRequest",
        ];
    }
    
    /*
     * Tripadvisor handle
     */
    
    public function getTripadvisorReviewsPage() 
    {
        if(!empty($this->tripadvisorReviewsPage))
        {
            return $this->tripadvisorReviewsPage;
        }
        if($cachedResponse = $this->getCachedResponse('tripadvisorReviewsPage'))
        {
            return $this->tripadvisorReviewsPage = $cachedResponse;
        }
        if($this->tripadvisor_url && $this->checkUrl($this->tripadvisor_url))
        {
            $tripadvisorPageContent = $this->getPageContent($this->tripadvisor_url, []);
            if($tripadvisorPageContent)
            {
                $this->setCachedResponse('tripadvisorReviewsPage', $tripadvisorPageContent);
                return $this->tripadvisorReviewsPage = $tripadvisorPageContent;
            }
        }
        return $this->tripadvisorReviewsPage;
    }
    
    public function saveTripadvisorReviews()
    {
        $count = $this->votes()->where('remote_type', SpotVote::TYPE_TRIPADVISOR)->count();
        $result = null;
        if($count < 5)
        {
            $page = $this->getTripadvisorReviewsPage();
            if($page)
            {
                $idsArr = [];
                foreach( $page->find('.reviewSelector') as $reviewObj )
                {
                    $id = $reviewObj->getAttribute('id');
                    $idsArr[] = str_replace('review_', '', $id);
                }
                $reviewsNeeded = 5 - $count;
                $reviewsAdded = 0;
                $reviews = [];
                foreach( $page->find('.basic_review') as $reviewObj )
                {
                    if($this->votes()->where('remote_type', SpotVote::TYPE_TRIPADVISOR)->count() >= 5)
                    {
                        break;
                    }
                    $message = $reviewObj->find('.partial_entry', 0);
                    if(empty($message) || empty($message->innertext()))
                    {
                        continue;
                    }
                    $removeFromMsg = $message->find('.partnerRvw', 0);
                    if($removeFromMsg)
                    {
                        $removeFromMsg->outertext = '';
                    }
                    $ratingObj = $reviewObj->find('.rating', 0);
                    $img = ($ratingObj)?$ratingObj->find('img', 0):null;
                    $rating = ($img)?preg_replace("/[^0-9]/", '', $img->class)/10:null;
                    if(empty($rating))
                    {
                        $spanObj = $ratingObj->find('.ui_bubble_rating', 0);
                        $rating = ($spanObj) ? preg_replace("/[^0-9]/", '', $spanObj->class)/10:null;
                    }
                    if(empty($rating))
                    {
                        continue;
                    }
                    $vote = $rating;
                    $helpObj = $reviewObj->find('.rnd_white_thank_btn', 0);
                    $remote_id = preg_replace("/[^0-9]/", '', $helpObj->class);
                    if(empty($remote_id))
                    {
                        $tooltipObj = $reviewObj->find('.tooltips .taLnk', 0);
                        $remote_id = ($tooltipObj) ? preg_replace("/[^0-9]/", '', $tooltipObj->id): null;
                    }
                    if(empty($remote_id) || $this->votes()->where('remote_id', 'ta_' . $remote_id)->exists())
                    {
                        continue;
                    }
                    $remote_user_container = $reviewObj->find('.username', 0);
                    if($remote_user_container->find('span', 0))
                    {
                        $remote_user = $remote_user_container->find('span', 0);
                    }
                    else
                    {
                        $remote_user = $remote_user_container;
                    }
                    $date = (new Carbon($reviewObj->find('.ratingDate', 0)->getAttribute('title')))->toDateTimeString();
                    $reviews[] = [
                        'vote' => round(floatval($vote)),
                        'message' => $message->innertext(),
                        'remote_id' => 'ta_' . $remote_id,
                        'remote_type' => SpotVote::TYPE_TRIPADVISOR,
                        'remote_user_name' => SpotVote::remoteReviewerNameCheck(trim($remote_user->innertext())),
                        //'remote_user_avatar' => $reviewObj->find('.avatar img', 0) ? $reviewObj->find('.avatar img', 0)->getAttribute('src') : null,
                        'created_at' => $date,
                        'updated_at' => $date,
                        'spot_id' => $this->id,
                    ];
                    $reviewsAdded++;
                    if($reviewsAdded >= $reviewsNeeded)
                    {
                        break;
                    }
                }
                $this->votes()->insert($reviews);
                
                $result = $reviews;
            }
            
        }
        return $result;
    }
    
    public function getTripadvisorReviewsCount()
    {
        $result = null;
        $page = $this->getTripadvisorReviewsPage();
        if($page)
        {
            $element = $page->find('.heading_ratings .more', 0);
            if(!$element)
            {
                $element = $page->find('.rating .more', 0);
            }
            if($element)
            {
                $result = intval($element->getAttribute('content'));
            }
        }
        return $result;
    }
    
    public function getTripadvisorRating() 
    {
        $result = null;
        $page = $this->getTripadvisorReviewsPage();
        if($page)
        {
            $element = $page->find('.heading_ratings .ui_bubble_rating', 0);
            $result = ($element) ? floatval(str_replace(',', '.', $element->getAttribute('content'))) : null;
            if(!$result)
            {
                $element = $page->find('.heading_ratings .rating_rr_fill', 0);
                $result = ($element) ? floatval($element->getAttribute('content')) : null;
            }
        }
        return $result;
    }
    
    /*
     * API's and parser's 
     * common methods
     */
    
    public function getResponse($client, $url, $options, $json = false)
    {
        try {
            $content = $client->get($url, $options); 
        }
        catch(Exception $e)
        {
            $content = false;
        }
        if($content)
        {
            if($json)
            {
                return json_decode($content->getBody()->getContents(), true);
            }
            return new \Htmldom($content->getBody()->getContents());
        }
        return false;
    }
    
    public function checkUrl($url)
    {
        if(!empty($url))
        {
            return filter_var($url, FILTER_VALIDATE_URL);
        }
        return false;
    }
    
    public function getPageContent($url, $options = [], $json = false)
    {
        $client = new Client([
            'cookies' => true, 
            'http_errors' => false
        ]);
        
        return $this->getResponse($client, $url, $options, $json);
    }
    
    public function getCachedResponse($name)
    {
        return Cache::get('response.' . $name . '.' . $this->id);
    }
    
    public function setCachedResponse($name, $data, $expires = null)
    {
        $exp = ($expires) ? $expires : $this->cacheExpiresDate;
        Cache::put('response.' . $name . '.' . $this->id, $data, $exp);
    }
}
