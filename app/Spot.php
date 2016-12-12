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
use DB;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Organizer;
use Request;
use Log;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;
use Facebook\Exceptions\FacebookSDKException;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBody;

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
        'share_links'
    ];

    protected $with = ['category.type', 'points'];

    protected $hidden = ['cover_file_name', 'cover_file_size', 'cover_content_type'];

    protected $casts = [
        'web_sites' => 'array',
        'videos' => 'array'
    ];

    protected $dates = ['start_date', 'end_date'];

    public $exceptCacheAttributes = [
        'is_favorite',
        'is_saved',
        'is_rated'
    ];
    
    protected $spotExtension = null;
    protected $googlePlacesInfo = null;
    protected $yelpInfo = null;
    protected $yelpToken = null;
    protected $bookingPage = null;

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
        
        parent::__construct($attributes);
    }

    /**
     * Get urls of 3 cover sizes
     */
    public function getCoverUrlAttribute()
    {
		$covers = [];
		foreach ( $this->remotePhotos()->get() as $rph ) {
			if ( $rph->image_type == 1 ) {
				$url = $rph->url;
				$covers = [
					"original" => $url,
    				"medium" => $url,
    				"thumb" => $url
				];
			}
		}
		if ( !$covers ) {
			$covers = $this->getPictureUrls('cover');
		}

		return $covers;
    }

    /**
     * Get spot rating
     *
     * @return float
     */
    public function getRatingAttribute()
    {
        return (float)$this->votes()->avg('vote');
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
    public function amenities()
    {
        return $this->hasMany(SpotAmenity::class);
    }
    
    /**
     * Get hotel spot info
     *
     * @return query
     */
    public function hotel()
    {
        return $this->hasOne(SpotHotel::class);
    }
    
    public function scopeHotels($query)
    {
        $spotTypeCategory = SpotTypeCategory::where('name', 'hotels')->first();
        
        return $query->where('spot_type_category_id', $spotTypeCategory->id);
    }
    
    /**
     * Get restaurant spot info
     *
     * @return query
     */
    public function restaurant()
    {
        return $this->hasOne(SpotRestaurant::class);
    }
    
    public function scopeRestaurants($query)
    {
        $spotTypeCategory = SpotTypeCategory::where('name', 'restaurants')->first();
        
        return $query->where('spot_type_category_id', $spotTypeCategory->id);
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
        return $this->hasMany(SpotPhoto::class);
    }

    /**
     * Get remote photos
     */
    public function remotePhotos()
    {
        return $this->morphMany(RemotePhoto::class, 'associated');
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
            ->whereRaw("(LOWER(\"title\") like LOWER('%$filter%') OR LOWER(\"description\") like LOWER('%$filter%'))");
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
    
    public function getHotelsQuery($fromString = false, $toString = false)
    {
        $hotelsQuery = [
            'pos' => 'HCOM_US',
            'locale' => 'en_US',
            'q-room-0-adults' => 1,
            'q-room-0-children' => 0,
            'tab' => 'description'
        ];
        
        if ($fromString && $toString)
        {
            $hotelsQuery['q-check-in']  = $fromString;
            $hotelsQuery['q-check-out'] = $toString;
        }
        
        return http_build_query($hotelsQuery);
    }
    
    public function getHotelsUrl($url, $fromString = false, $toString = false)
    {
        if($this->checkUrl($url))
        {
            return $url . '?' . $this->getHotelsQuery($fromString, $toString);
        }
        return false;
    }
    
    public function getHotelsPrice($hotelsRes)
    {
        if( $hotelsPriceObj = $hotelsRes->find('span.current-price', 0))
        {
            return $hotelsPriceObj->innertext();
        }
        elseif( $hotelsPriceObj = $hotelsRes->find('meta[itemprop=priceRange]', 0) )
        {
            $hotelsPrice = explode(' ' , $hotelsPriceObj->getAttribute('content'));
            return array_pop($hotelsPrice);
        }
        return false;
    }
    
    /*
     * Booking.com parser 
     */
    
    public function getBookingQuery($fromString = false, $toString = false, $withDates = false)
    {
        $bookingQuery  = [
            'room1'             => 'A',
            'selected_currency' => 'USD',
            'changed_currency'  => 1,
            'top_currency'      => 1, 
            'lang'              => 'en-us'
        ];
        if($withDates)
        {
            $bookingQuery['checkin'] = $fromString;
            $bookingQuery['checkout'] = $toString;
        }
        return http_build_query($bookingQuery);
    }
    
    public function getBookingUrl($url, $fromString = false, $toString = false, $withDates = false)
    {
        if($this->checkUrl($url))
        {
            $query = '?' . $this->getBookingQuery($fromString, $toString, $withDates);
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
            if( ($price && ( $newPrice < $price )) || !$price )
            {
                $price = $newPrice;
            }
        }
        $result = $price;
        if( $price )
        {
            $result = '$' . $price;
        }
        if( empty($result) && ($bookingPriceObj = $bookingRes->find('meta[itemprop=priceRange]', 0)) )
        {
            $result = explode(' ' , $bookingPriceObj->getAttribute('content'));
            $result = str_replace('US', '', array_pop($result));
        }
        return $result;
    }
    
    public function getBookingPage()
    {
        if(!empty($this->bookingPage))
        {
            return $this->bookingPage;
        }
        $spotInfo = $this->getSpotExtension();
        if(!empty($spotInfo->booking_url))
        {
            $url = $this->getBookingUrl($spotInfo->booking_url);
            if($url)
            {
                $bookingPageContent = $this->getPageContent($url, [
                    'headers' => $this->getBookingHeaders()
                ]);
                if($bookingPageContent)
                {
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
        return collect($result);
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
        if( ( $bookingAmenities = $bookingRes->find('div.facilitiesChecklist', 0) ) && $this->amenities()->count() == 0 )
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
                        $this->amenities()->save($amenity);
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
            $reviewsUrl     = end( $reviewsUrlArr );
            $reviewsUrl     = preg_replace( '#\..?.?.?.?.?\.?html#' , '' , $reviewsUrl);
            $cc1            = $reviewsUrlArr[count($reviewsUrlArr) - 2];
            $url = 'http://www.booking.com/reviewlist.html?pagename=' . $reviewsUrl . '&cc1=' . $cc1 . '&rows=100';
            $this->booking_reviews_url = $url;
            return $this->booking_reviews_url;
        }
        return false;
    }
    
    public function getBookingTotals()
    {
        $result = null;
        $pageContent = $this->getBookingPage();
        if($pageContent)
        {
            $ratingObj = $pageContent->find('#review_list_main_score', 0);
            if(!empty($ratingObj))
            {
                $value = trim($ratingObj->innertext());
                $value = round(((float) str_replace(',', '.', $value))/2, 1);
                $result['rating'] = $value;
            }
            $countObj = $pageContent->find('#review_list_score_count strong', 0);
            if(!empty($countObj))
            {
                $countValue = intval($countObj->innertext());
                $result['reviews_count'] = $countValue;
            }
        }
        return $result;
    }
    
    public function getBookingReviews($reviewsContent, $save = false)
    {
        $result = null;
        foreach( $reviewsContent->find('.review_item') as $reviewObj )
        {
            $reviewTextObj = $reviewObj->find('.review_item_review_content', 0);
            if(!$reviewTextObj)
            {
                continue;
            }
            foreach($reviewTextObj->find('.review_item_icon') as $icon)
            {
                $icon->outertext = "";
            }
            if( $noCommentsObj = $reviewTextObj->find('.review_none', 0))
            {
                $noCommentsObj->outertext = "";
            }
            $message = trim($reviewTextObj->innertext());
            if(empty($message))
            {
                continue;
            }
            $item = new SpotVote();
            $idObj = $reviewObj->find('input[name=review_url]', 0);
            $item->remote_id = 'bk_' . $idObj->getAttribute( 'value' );
            $item->message = html_entity_decode($message);
            $scoreObj = $reviewObj->find('.review_item_review_score', 0);
            $item->vote = round((float)$scoreObj->innertext()/2);
            $item->remote_user_name = trim($reviewObj->find('h4', 0)->innertext);
            $item->remote_user_avatar = str_replace('height=64&width=64', 'height=300&width=300', $reviewObj->find('.avatar-mask', 0)->getAttribute('src'));
            $item->remote_type = SpotVote::TYPE_BOOKING;
            if( $save && !SpotVote::where('remote_id', $item->remote_id)->exists())
            {
                $this->votes()->save($item);
            }
            $result[] = $item;
        }
        return $result;
    }
    
    public function getBookingCover($bookingRes)
    {
        $result = null;
        
        $query = RemotePhoto::where('associated_type', Spot::class)
                ->where('associated_id', $this->id)
                ->where('image_type', 1);
        if( $query->exists() )
        {
            $result = $query->first();
        }
        else
        {
            if( $bookingSlider = $bookingRes->find('.hp-gallery-slides', 0) )
            {
                if($picture = $bookingSlider->find('img', 0))
                {
                    $url = $picture->getAttribute('src');
                    if(empty($url))
                    {
                        $url = $picture->getAttribute('data-lazy');
                    }
                    $filename = $this->getFilenameFromUrl($url);
                    if(!RemotePhoto::where('url', 'like' , "%$filename%")
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists())
                    {
                        $result = new RemotePhoto([
                            'url' => $url,
                            'image_type' => 1,
                            'size' => 'original',
                        ]);
                        $this->remotePhotos()->save( $result );
                    }
                }
            }
            elseif( $bookingSlider = $bookingRes->find('.bh-photo-grid', 0) )
            {
                if($picture = $bookingSlider->find('a.active-image', 0))
                {
                    $url = $picture->getAttribute('href');
                    $filename = $this->getFilenameFromUrl($url);
                    if( $filename && !RemotePhoto::where('url', 'like' , "%$filename%")
                        ->where('associated_type', Spot::class)
                        ->where('associated_id', $this->id)
                        ->exists() )
                    {
                        $result = new RemotePhoto([
                            'url' => $url,
                            'image_type' => 1,
                            'size' => 'original',
                        ]);
                        $this->remotePhotos()->save( $result );
                    }
                }
            }
        }
        return $result;
    }
    
    /*
     * Google places methods 
     */
    
    public function getGooglePid()
    {
        $googlePid = false;
        $spotInfo  = $this->getSpotExtension();
        if($spotInfo)
        {
            $googlePid = (!empty($spotInfo->google_pid)
                    && $spotInfo->google_pid != 'null' 
                    && $spotInfo->google_pid != '0')?$spotInfo->google_pid:false;
        }
        return $googlePid;
    }
    
    public function getGooglePlaceInfo()
    {
        if($this->googlePlacesInfo)
        {
            $this->googlePlacesInfo;
        }
        $googlePid = $this->getGooglePid();
        if($googlePid)
        {
            try
            {
                $url = 'https://maps.googleapis.com/maps/api/place/details/json?placeid=' . $googlePid . '&key=' . config('google.places.key');
                $response = $this->getPageContent($url, [], true);
                if($response['status'] == "OK")
                {
                    $this->googlePlacesInfo = $response['result'];
                }
            }
            catch(Exception $e) {/* googlePlacesInfo already null */}
        }
        return $this->googlePlacesInfo;
    }
    
    public function getGoogleReviewsInfo()
    {
        $googlePlaceInfo = $this->getGooglePlaceInfo();
        $result = null;
        if(!empty($googlePlaceInfo['rating']) && !empty($googlePlaceInfo['reviews']))
        {
            $result = [
                'rating' => $googlePlaceInfo['rating'],
                'reviews_count' => count($googlePlaceInfo['reviews'])
            ];
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
                $itemObj->remote_user_name = $item['author_name'];
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
    
    public function saveGooglePlacePhotos( $save = false)
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
            $this->restaurant->hours = $openingHours;
            $this->restaurant->save();
            $result = $openingHours;
        }
        return $result;
    }
    
    protected function getGooglePhotoUrl($photo_reference)
    {
        return config('google.places.baseUri') . 'photo'
        . '?maxwidth=400'
        . '&photoreference=' . $photo_reference
        . '&key=' . config('google.places.key');
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
        return $id;
    }
    
    public function getFacebookRating()
    {
        $fb = app(LaravelFacebookSdk::class);
        $spotExtention = $this->getSpotExtension();
        if($spotExtention && $this->checkUrl($spotExtention->facebook_url))
        {
            $id = $this->getFacebookIdFromUrl($spotExtention->facebook_url);
            if($id)
            {
                try
                {
                    $response = $fb->get('/' . $id . '?fields=rating_count,overall_star_rating,name', config('laravel-facebook-sdk.app_token'));
                    $values = $response->getGraphNode()->asArray();
                    return [
                        'reviews_count' => $values['rating_count'],
                        'rating'        => $values['overall_star_rating']
                    ];
                }
                catch (Exception $e) {}
                catch (FacebookSDKException $e) {}
            }
        }
        return null;
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
        $client = new Client();
        $body = new PostBody();
        $body->forceMultipartUpload(true);
        $body->replaceFields([
                    'client_id' => config('yelp-api.client_id'),
                    'client_secret' => config('yelp-api.client_secret'),
                    'grant_type' => 'client_credentials'
                ]);
        $options = ['body' => $body];
        try
        {
            $response = $client->post('https://api.yelp.com/oauth2/token', $options);
            $this->yelpToken =  json_decode($response->getBody()->getContents(), true)['access_token'];
        }
        catch(Exception $e) { }
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
    
    public function getYelpInfo()
    {
        if(!empty($this->yelpInfo))
        {
            return $this->yelpInfo;
        }
        $spotInfo = $this->getSpotExtension();
        if($spotInfo && !empty($spotInfo->yelp_url))
        {
            $token = $this->getYelpToken();
            $id    = $this->getYelpIdFromUrl($spotInfo->yelp_url);
            if($token && $id)
            {
                $client = new Client();
                $headers = ['Authorization' => 'Bearer ' . $token];
                $url = 'https://api.yelp.com/v3/businesses/' . $id;
                try
                {
                    $response = $client->get($url , [
                        'headers' => $headers
                    ]);
                    $responseArray = json_decode($response->getBody()->getContents(), true);
                    if(!empty($responseArray))
                    {
                        $this->yelpInfo = $responseArray;
                    }
                }
                catch(RequestException $e) { }
            }
            
        }
        return $this->yelpInfo;
    }
    
    public function getYelpBizInfo()
    {
        $result = null;
        $spotInfo = $this->getSpotExtension();
        if($spotInfo && !empty($spotInfo->yelp_url))
        {
            $token = $this->getYelpToken();
            $id    = $this->getYelpIdFromUrl($spotInfo->yelp_url);
            if($token && $id)
            {
                $client = new Client();
                $headers = ['Authorization' => 'Bearer ' . $token];
                $url = 'https://api.yelp.com/v3/businesses/' . $id;
                try
                {
                    $response = $client->get($url , [
                        'headers' => $headers
                    ]);
                    $responseArray = json_decode($response->getBody()->getContents(), true);
                    if( !empty($responseArray['review_count']) && !empty($responseArray['rating']))
                    {
                        $result = [
                            'reviews_count' => $responseArray['review_count'],
                            'rating'        => $responseArray['rating']
                        ];
                    }
                }
                catch(RequestException $e) { }
            }
            
        }
        return $result;
    }
    
    public function getYelpReviewsFromApi($save = false)
    {
        $result = null;
        $spotInfo = $this->getSpotExtension();
        if($spotInfo && !empty($spotInfo->yelp_url))
        {
            $token = $this->getYelpToken();
            $id    = $this->getYelpIdFromUrl($spotInfo->yelp_url);
            if($token && $id)
            {
                $client = new Client();
                $headers = ['Authorization' => 'Bearer ' . $token];
                $url = 'https://api.yelp.com/v3/businesses/' . $id;
                try
                {
                    $response = $client->get($url . '/reviews', [
                        'headers' => $headers
                    ]);
                    $yelp_reviews = json_decode($response->getBody()->getContents(), true);
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
                            $item->remote_user_name = $review['user']['name'];
                            $item->remote_user_avatar = $review['user']['image_url'];
                            if( $save && !SpotVote::where('remote_id', $remote_id)->exists())
                            {
                                $this->votes()->save($item);
                            }
                            $result[] = $item;
                        }
                    }
                }
                catch(RequestException $e) { }
            }
        }
        return $result;
    }
    
    public function getYelpReviewsFromPage($save = false)
    {
        $result = null;
        $spotInfo = $this->getSpotExtension();
        if($spotInfo && $this->checkUrl($spotInfo->yelp_url))
        {
            $url = $spotInfo->yelp_url;
            $headers = $this->getYelpHeaders($url);
            $client = new Client();
            try
            {
                $response = $client->get($url . '/review_feed/', [
                    'headers' => $headers
                ]);
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
                        $item->remote_user_name = $sidebar->find('.user-display-name', 0)->innertext;
                        $item->remote_user_avatar = str_replace('60s.', '300s.', $sidebar->find('.photo-box img', 0)->src);
                        if( $save && !SpotVote::where('remote_id', $remoteId)->exists())
                        {
                            $this->votes()->save($item);
                        }
                        $result[] = $item;
                    }
                    //$result = $htmlString;
                }
            }
            catch(RequestException $e) { }
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
     * Reviews totals
     */
    
    public function getReviewsTotal($saveReviews = true)
    {
        $result = [];
        if( $facebookRating = $this->getFacebookRating())
        {
            $result['info']['facebook'] = $facebookRating;
        }
        if(!empty($this->getGooglePlaceInfo()))
        {
            $this->getGoogleReviews($saveReviews);
            $result['info']['google'] = $this->getGoogleReviewsInfo();
        }
        $yelpInfo = $this->getYelpBizInfo();
        if( !empty($yelpInfo) )
        {
            $this->getYelpReviewsFromPage($saveReviews);
            $result['info']['yelp'] = $yelpInfo;
        }
        $spotInfo = $this->getSpotExtension();
        if(!empty($spotInfo->booking_url))
        {
            $reviewsUrl = $this->getBookingReviewsUrl($spotInfo->booking_url);
            if($reviewsUrl && $reviewsPageContent = $this->getPageContent($reviewsUrl, [
                'headers' => $this->getBookingHeaders()
            ]))
            {
                $this->getBookingReviews($reviewsPageContent, $saveReviews);
            }
            $bookingTotals = $this->getBookingTotals();
            if(!empty($bookingTotals))
            {
                $result['info']['booking'] = $bookingTotals;
            }
        }
        if(!empty($spotInfo->tripadvisor_rating) && !empty($spotInfo->tripadvisor_reviews_count))
        {
            $result['info']['tripadvisor'] = [
                'rating' => (float)$spotInfo->tripadvisor_rating,
                'reviews_count' => (int)$spotInfo->tripadvisor_reviews_count
            ];
        }
        
        if($zoomReviewCount = $this->votes()->whereNull('remote_type')->whereNull('remote_id')->count())
        {
            $result['info']['zoomtivity'] = [
                'rating' => round((float)$this->votes()
                        ->whereNull('remote_type')
                        ->whereNull('remote_id')
                        ->avg('vote'), 1),
                'reviews_count' => $zoomReviewCount
            ];
        }
        
        $starsSumm = 0;
        $reviewsCount = 0;
        if( !empty($result['info']) )
        {
            foreach($result['info'] as $index => $service)
            {
                if(empty($service))
                {
                    unset($result['info'][$index]);
                    continue;
                }
                $starsSumm += $service['rating'];
                $reviewsCount += $service['reviews_count'];
            }
            $starsSumm = $starsSumm/count($result['info']);
        }
        $result['total']['rating'] = round((float)$starsSumm, 1);
        $result['total']['reviews_count'] = $reviewsCount;
        
        
        return $result;
    }
    
    /*
     * API's and parser's 
     * common methods
     */
    
    public function getSpotExtension()
    {
        if($this->spotExtension)
        {
            return $this->spotExtension;
        }
        if(!empty($this->hotel))
        {
            $this->spotExtension = $this->hotel;
        }
        if(!empty($this->restaurant))
        {
            $this->spotExtension = $this->restaurant;
        }
        return $this->spotExtension;
    }
    
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
    
    
    
    
    
}
