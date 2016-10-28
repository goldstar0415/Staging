<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hotel\HotelDestroyRequest;
use App\Http\Requests\Hotel\HotelIndexRequest;
use App\Http\Requests\Hotel\HotelStoreRequest;
use App\Http\Requests\Hotel\HotelUpdateRequest;
use App\Services\Privacy;
use App\Spot;
use App\SpotAmenity;
use App\SpotVote;
use App\RemotePhoto;
use App\SpotTypeCategory;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use GuzzleHttp\Client;


/**
 * Class HotelController
 * @package App\Http\Controllers
 *
 * Hotel resource controller
 */
class HotelController extends Controller
{
    
    /**
     * @var Guard
     */
    private $auth;
        
    /**
     * HotelController constructor.
     */
    public function __construct(Guard $auth)
    {
        $this->middleware('auth');
        $this->auth = $auth;
    }

    /**
     * Display a listing of the hotels.
     * @param HotelIndexRequest $request
     * @param Privacy $privacy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(HotelIndexRequest $request, Privacy $privacy)
    {

        $spotTypeCategory = SpotTypeCategory::where('name', 'hotels')->first();
            
        $hotels = Spot::orderBy('id', 'asc')
                    ->where('spot_type_category_id', $spotTypeCategory->id)
                    ->with('remotePhotos', 'hotel', 'amenities'); 

        return $this->paginatealbe($request, $hotels, 15);
    }

    /**
     * Store a newly created hotel in storage.
     * @param HotelStoreRequest $request
     * @return Hotel
     */
    public function store(HotelStoreRequest $request)
    {
        $hotel = new Spot($request->except([
            'description',
        ]));

        if ($request->has('description')) {
            $hotel->description = e($request->description);
        }

        return $hotel;
    }

    /**
     * Display the specified hotel.
     *
     * @param  Hotel $hotel
     * @return $this
     */
    public function show($hotel)
    {
        
        $googlePlaceInfo = $hotel->getGooglePlaceInfo();
        $hotel->google_response = $googlePlaceInfo;
        
        $amenitiesArray = [];
        if($hotel->hotel && !$hotel->hotel->is_booking_parsed && $this->checkUrl($hotel->hotel->booking_url))
        {
            $hotelInfo = $hotel->hotel;
            $bookingUrl = $this->getBookingUrl($hotel->hotel->booking_url);
            if($bookingUrl)
            {
                $bookingPageContent = $this->getPageContent($bookingUrl, [
                    'headers' => $this->getBookingHeaders()
                ]);
                if($bookingPageContent)
                {
                    $remote_photos = false;
                    if( $remote_photos = $this->saveBookingPhotos($bookingPageContent, $hotel) )
                    {
                        $hotel->remote_photos = $hotel->remotePhotos->merge($remote_photos);
                    }
                    $amenities = false;
                    if( $amenities = $this->saveBookingAmenities($bookingPageContent, $hotel) )
                    {
                        $amenitiesArray = $amenities;
                    }

                    $reviewsUrl = $this->getReviewsUrl($hotel->hotel->booking_url);
                    $reviews = false;
                    if($reviewsUrl)
                    {
                        $reviewsPageContent = $this->getPageContent($reviewsUrl, [
                            'headers' => $this->getBookingHeaders()
                        ]);

                        $reviews = $this->saveReviews($reviewsPageContent, $hotel);
                    }
                    if($googlePlaceInfo)
                    {
                        $googlePhotos = $hotel->saveGooglePlacePhotos($googlePlaceInfo);
                        $remote_photos = (!$remote_photos) ? $googlePhotos : $googlePhotos->merge($remote_photos);
                        $googleReviews = $hotel->saveGooglePlaceReviews($googlePlaceInfo);
                        $reviews = (!$reviews) ? $googleReviews : $googleReviews->merge($reviews);
                    }
                    if($remote_photos || $amenities || $reviews)
                    {
                        $hotelInfo->is_booking_parsed = true;
                        $hotelInfo->save();
                        $hotel->hotel = $hotelInfo;
                    }
                }
            }
        }
        
        foreach($hotel->amenities as $item)
        {
            $amenitiesArray[$item->title][] = $item->item;
        }
        $hotel->amenitiesArray = $amenitiesArray;
        
        return $hotel;
    }

    /**
     * Update the specified hotel in storage.
     *
     * @param  HotelUpdateRequest $request
     * @param  \App\Hotel $hotel
     * @return Hotel
     */
    public function update(HotelUpdateRequest $request, $hotel)
    {
        $hotel->update($request->except(['description']));
        $hotel->description = $request->has('description') ? e($request->description) : '';
        $hotel->save();

        return $hotel;
    }

    /**
     * Remove the specified hotel from storage.
     *
     * @param HotelDestroyRequest $request
     * @param Hotel $hotel
     * @return bool|null
     */
    public function destroy(HotelDestroyRequest $request, $hotel)
    {
        return ['result' => $hotel->delete()];
    }
    
    
    public function prices (Request $request, Spot $hotel)
    {
        $result       = [];
        $hotelInfo    = $hotel->hotel;
        $dates        = $request->all();
        $from         = date_parse_from_format( 'm.d.Y' , $dates['start_date'] );
        $to           = date_parse_from_format( 'm.d.Y' , $dates['end_date'] );
        
        $result['dates'] = [
            'from' => $from,
            'to'   => $to,
        ];
        
        $result['data']['amenitiesArray'] = [];
        $result['data']['hotels'] = false;
        $result['data']['booking'] = false;
        
        $fromString   = $from['year'] . '-' . (strlen($from['month']) == 1?'0':'') . $from['month'] . '-' . (strlen($from['day']) == 1?'0':'') . $from['day'];
        $toString     =   $to['year'] . '-' . (strlen(  $to['month']) == 1?'0':'') .   $to['month'] . '-' . (strlen(  $to['day']) == 1?'0':'') .   $to['day'];
        
        
        // Hotels.com parser
        
        $result['data']['hotelsUrl'] = $this->getHotelsUrl($hotelInfo->hotelscom_url, $fromString, $toString);
        
        if($result['data']['hotelsUrl'])
        {
            $hotelsPageContent = $this->getPageContent($result['data']['hotelsUrl']);
            
            if($hotelsPageContent)
            {
                $result['data']['hotels'] = $this->getHotelsPrice($hotelsPageContent);
            }
        }
        
        //Booking.com parser
        
        $result['data']['bookingUrl'] = $this->getBookingUrl($hotelInfo->booking_url, $fromString, $toString, true);
        if($result['data']['bookingUrl'])
        {
            $bookingPageContent = $this->getPageContent($result['data']['bookingUrl'], [
                'headers' => $this->getBookingHeaders()
            ]);
            if($bookingPageContent)
            {
                $result['data']['booking'] = $this->getBookingPrice($bookingPageContent);
            }
        }

        $result['result'] = $hotel;
        return $result;
    }
    
    protected function checkUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    protected function getHotelsQuery($fromString, $toString)
    {
        $hotelsQuery = [
            'pos' => 'HCOM_US',
            'locale' => 'en_US',
            'q-check-in' => $fromString,
            'q-check-out' => $toString,
            'q-room-0-adults' => 1,
            'q-room-0-children' => 0,
            'tab' => 'description'
        ];
        return http_build_query($hotelsQuery);
    }
    
    protected function getHotelsUrl($url, $fromString, $toString)
    {
        if($this->checkUrl($url))
        {
            return $url . '?' . $this->getHotelsQuery($fromString, $toString);
        }
        return false;
    }
    
    protected function getHotelsPrice($hotelsRes)
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
    
    protected function getBookingQuery($fromString = false, $toString = false, $withDates = false)
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
    
    protected function getBookingUrl($url, $fromString = false, $toString = false, $withDates = false)
    {
        if($this->checkUrl($url))
        {
            $query = '?' . $this->getBookingQuery($fromString, $toString, $withDates);
            return preg_replace( '#\..?.?.?.?.?\.?html#' , '.html' , $url) . $query;
        }
        return false;
    }
    
    protected function getBookingHeaders()
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
    
    protected function getBookingPrice($bookingRes)
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
    
    protected function saveBookingPhotos($bookingRes, $hotel)
    {
        $result = [];
        if( $bookingSlider = $bookingRes->find('.hp-gallery-slides', 0) )
        {
            foreach( $bookingSlider->find('img') as $picture )
            {
                $url = $picture->getAttribute('src');
                if(empty($url))
                {
                    $url = $picture->getAttribute('data-lazy');
                }
                if( !RemotePhoto::where('url', $url)->exists() )
                {
                    $result[] = new RemotePhoto([
                        'url' => $url,
                        'image_type' => 0,
                        'size' => 'original',
                    ]);
                }
            }
            $hotel->remotePhotos()->saveMany( $result );
        }
        return collect($result);
    }
    
    protected function saveBookingAmenities($bookingRes, $hotel)
    {
        $result = [];
        if( ( $bookingAmenities = $bookingRes->find('div.facilitiesChecklist', 0) ) && $hotel->amenities()->count() == 0 )
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
                    if( !SpotAmenity::where('spot_id', $hotel->id)
                                     ->where('title', $sectionTitle)
                                     ->where('item', $body)->exists() )
                    $amenity = new SpotAmenity([
                        'title' => $sectionTitle, 
                        'item' => $body,
                        'spot_id' => $hotel->id
                    ]);
                    $amenity->save();
                    $result[$sectionTitle][] = $body;
                }
            }
        }
        return $result;
    }


    protected function getReviewsUrl($booking_url)
    {
        if($this->checkUrl($booking_url))
        {
            $reviewsUrlArr  = explode( '/' , $booking_url);
            $reviewsUrl     = end( $reviewsUrlArr );
            $reviewsUrl     = preg_replace( '#\..?.?.?.?.?\.?html#' , '' , $reviewsUrl);
            $cc1            = $reviewsUrlArr[count($reviewsUrlArr) - 2];
            return 'http://www.booking.com/reviewlist.html?pagename=' . $reviewsUrl . ';cc1=' . $cc1;
        }
        return false;
    }
    
    protected function saveReviews($reviewsContent, $hotel)
    {
        $result = [];
        foreach( $reviewsContent->find('.review_item') as $reviewObj )
        {
            $reviewTextObj = $reviewObj->find('.review_pos', 0);
            if(!$reviewTextObj)
            {
                continue;
            }
            $item = new SpotVote();
            $idObj = $reviewObj->find('input[name=review_url]', 0);
            $item->remote_id = 'bk_' . $idObj->getAttribute ( 'value' );
            if( !SpotVote::where('remote_id', $item->remote_id)->exists())
            {
                $sign = $reviewTextObj->find('.review_item_icon', 0);
                $sign->outertext = '';
                $item->message = $reviewTextObj->innertext();
                $scoreObj = $reviewObj->find('.review_item_review_score', 0);
                $item->vote = round((float)$scoreObj->innertext()/2);
                $hotel->votes()->save($item);
                $result[] = $item;
            }
        }
        return collect($result);
    }
    
    protected function getPageContent($url, $options = [])
    {
        $client = new Client([
            'cookies' => true, 
            'http_errors' => false
        ]);
        
        return $this->getResponse($client, $url, $options);
    }
    
    public function getResponse($client, $url, $options)
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
            return new \Htmldom($content->getBody()->getContents());
        }
        return false;
    }
    
}
