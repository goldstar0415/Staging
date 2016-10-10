<?php

namespace App\Http\Controllers;

use App\Events\OnHotelCreate;
use App\Events\OnHotelUpdate;
use App\Http\Requests\Hotel\HotelDestroyRequest;
use App\Http\Requests\Hotel\HotelIndexRequest;
use App\Http\Requests\Hotel\HotelStoreRequest;
use App\Http\Requests\Hotel\HotelUpdateRequest;
use App\Services\Privacy;
use App\Hotel;
use App\HotelAmenity;
use App\RemotePhoto;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Client;

use App\Http\Requests;

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

        $hotels = Hotel::orderBy('id')->with('remotePhotos'); //::query();

        return $this->paginatealbe($request, $hotels, 15);
    }

    /**
     * Store a newly created hotel in storage.
     * @param HotelStoreRequest $request
     * @return Hotel
     */
    public function store(HotelStoreRequest $request)
    {
        $hotel = new Hotel($request->except([
            'desc_en',
        ]));

        if ($request->has('desc_en')) {
            $hotel->desc_en = e($request->desc_en);
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
        $amenitiesArray = [];
        foreach($hotel->amenities as $item)
        {
            $amenitiesArray[$item->hotel_name][] = $item->item;
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
        $hotel->update($request->except(['desc_en']));
        $hotel->desc_en = $request->has('desc_en') ? e($request->desc_en) : '';
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
    
    
    public function prices (Request $request, Hotel $hotel)
    {
        $dates        = $request->all();
        $from         = date_parse_from_format ( 'm.d.Y' , $dates['start_date'] );
        $to           = date_parse_from_format ( 'm.d.Y' , $dates['end_date'] );
        //$picsArr      = [];
        $amenitiesArr = [];
        
        $fromString   = $from['year'] . '-' . (strlen($from['month']) == 1?'0':'') . $from['month'] . '-' . (strlen($from['day']) == 1?'0':'') . $from['day'];
        $toString     =   $to['year'] . '-' . (strlen(  $to['month']) == 1?'0':'') .   $to['month'] . '-' . (strlen(  $to['day']) == 1?'0':'') .   $to['day'];
        $client       = new Client(['cookies' => true]); 
        
        
        // Hotels.com parser
        $hotelsPrice = false;
        $hotelsQuery = [
            'pos' => 'HCOM_US',
            'locale' => 'en_US',
            'q-check-in' => $fromString,
            'q-check-out' => $toString,
            'q-room-0-adults' => 1,
            'q-room-0-children' => 0,
            'tab' => 'description'
        ];
        $hotelsUrl = $hotel->hotelscom_url . '?' . http_build_query($hotelsQuery);
        
        try {
            $hotelsContent = $client->get($hotelsUrl); 
        }
        catch(ConnectException $e)
        {
            $hotelsContent = false;
        }
        
        if($hotelsContent)
        {
            $hotelsRes = new \Htmldom($hotelsContent->getBody()->getContents());

            if( $hotelsPriceObj = $hotelsRes->find('span.current-price', 0))
            {
                $hotelsPrice = $hotelsPriceObj->innertext();
            }
            elseif( $hotelsPriceObj = $hotelsRes->find('meta[itemprop=priceRange]', 0) )
            {
                $hotelsPrice = explode(' ' , $hotelsPriceObj->getAttribute('content'));
                $hotelsPrice = array_pop($hotelsPrice);
            }
        }
        else {
            $hotelsPrice = false;
        }
        
        
        //Booking.com parser
        $bookingPrice = false;
        $bookingQuery  = [
            'checkin'           => $fromString, 
            'checkout'          => $toString,
            'room1'             => 'A',
            'selected_currency' => 'USD',
            'changed_currency'  => 1,
            'top_currency'      => 1, 
            'lang'              => 'en-us'
        ];
        $bookingUrl = preg_replace( '#\..?.?.?.?.?\.?html#' , '.html' , $hotel->booking_url) . '?' . http_build_query($bookingQuery);
        try {
            $bookingContent = $client->get($bookingUrl, [
                //'debug' => true,
                'headers' => [
                    'Host' => 'www.booking.com',
                    'Connection' => 'keep-alive',
                    'Cache-Control' => 'max-age=0',
                    'Upgrade-Insecure-Requests' => '1',
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate, sdch',
                    'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
                    'Cookie' => 'lastSeen=0',
                ]
            ]);
        }
        catch (ConnectException $e)
        {
            $bookingContent = false;
        }
        if($bookingContent)
        {
            $bookingContentString = $bookingContent->getBody()->getContents();
            $bookingRes = new \Htmldom($bookingContentString);
            $bookingPrice = false;
            $price = false;
            foreach($bookingRes->find('.rooms-table-room-price') as $bookingPriceObj)
            {
                $newPrice = trim(str_replace('US$', '', $bookingPriceObj->innertext()));
                if( ($price && ( $newPrice < $price )) || !$price )
                {
                    $price = $newPrice;
                    
                }
            }
            $bookingPrice = $price;
            if( $price )
            {
                $bookingPrice = '$' . $price;
            }
            if( empty($bookingPrice) && ($bookingPriceObj = $bookingRes->find('meta[itemprop=priceRange]', 0)) )
            {
                
                $bookingPrice = explode(' ' , $bookingPriceObj->getAttribute('content'));
                $bookingPrice = str_replace('US', '', array_pop($bookingPrice));
            }
            /*if( $bookingSlider = $bookingRes->find('.hp-gallery-slides', 0) )
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
                        $picsArr[] = new RemotePhoto([
                            'url' => $url,
                            'image_type' => 0,
                            'size' => 'original',
                        ]);
                    }
                }
                $hotel->remotePhotos()->saveMany( $picsArr );
            }
            $hotelChanged = false;
            if( ( $bookingDesc = $bookingRes->find('#summary', 0) ) && empty($hotel->description) )
            {
                $bookingDesc->find('.chain-content ', 0)->outertext = '';
                
                $hotel->description = $bookingDesc->innertext();
                $hotelChanged = true;
            }*/
            if( ( $bookingAmenities = $bookingRes->find('div.facilitiesChecklist', 0) ) && $hotel->amenities()->count() == 0 )
            {
                foreach( $bookingAmenities->find('.facilitiesChecklistSection') as $facilitiesChecklistSection )
                {
                    $sectionTitle = trim($facilitiesChecklistSection->find('h5', 0)->innertext());
                    foreach($facilitiesChecklistSection->find('li') as $sectionItem)
                    {
                        $body = trim($sectionItem->innertext());
                        if( !HotelAmenity::where('hotel_id', $hotel->id)
                                         ->where('title', $sectionTitle)
                                         ->where('item', $body)->exists() )
                        $amenity = new HotelAmenity([
                            'title' => $sectionTitle, 
                            'item' => $body,
                            'hotel_id' => $hotel->id
                        ]);
                        $amenity->save();
                        $amenitiesArr[$sectionTitle][] = $body;
                    }
                }
            }
            
            /*if($hotelChanged)
            {
                $hotel->save();
            }*/
            
        }
        else {
            $bookingPrice = false;
        }
                
        return [
            'result' => $hotel, 
            'dates'  => [
                'from' => $from, 
                'to'   => $to
            ],
            'data'   => [
                'hotels'            => $hotelsPrice, 
                'booking'           => $bookingPrice,
                'bookingUrl'        => $bookingUrl,
                'hotelsUrl'         => $hotelsUrl,
                //'remote_photos'     => $picsArr,      
                'amenitiesArray'    => $amenitiesArr,
            ] 
        ]; 
    }

}
