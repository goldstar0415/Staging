<?php

namespace App\Http\Controllers;

use App\Http\Requests\Restaurant\RestaurantDestroyRequest;
use App\Http\Requests\Restaurant\RestaurantIndexRequest;
use App\Http\Requests\Restaurant\RestaurantStoreRequest;
use App\Http\Requests\Restaurant\RestaurantUpdateRequest;
use App\Services\Privacy;
use App\Spot;
use App\SpotTypeCategory;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class RestaurantController
 * @package App\Http\Controllers
 *
 * Restaurant resource controller
 */
class RestaurantController extends Controller
{
    
    /**
     * @var Guard
     */
    private $auth;
        
    /**
     * RestaurantController constructor.
     */
    public function __construct(Guard $auth)
    {
        $this->middleware('auth');
        $this->auth = $auth;
    }

    /**
     * Display a listing of the restaurants.
     * @param RestaurantIndexRequest $request
     * @param Privacy $privacy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(RestaurantIndexRequest $request, Privacy $privacy)
    {
        $restaurants = Spot::orderBy('id', 'asc')
                    ->restaurants()
                    ->with('remotePhotos', 'restaurant', 'amenities'); 

        return $this->paginatealbe($request, $restaurants, 15);
    }

    /**
     * Store a newly created restaurant in storage.
     * @param RestaurantStoreRequest $request
     * @return Spot
     */
    public function store(RestaurantStoreRequest $request)
    {
        $restaurant = new Spot($request->except([
            'description',
        ]));

        if ($request->has('description')) {
            $restaurant->description = e($request->description);
        }

        return $restaurant;
    }

    /**
     * Display the specified restaurant.
     *
     * @param  Spot $restaurant
     * @return $this
     */
    public function show($restaurant)
    {
        
        $amenitiesArray = [];
        if($restaurant->restaurant && !$restaurant->restaurant->is_parsed )
        {
            $googlePlaceInfo = $restaurant->getGooglePlaceInfo();
            $restaurant->google_response = $googlePlaceInfo;
            $restaurantInfo = $restaurant->restaurant;

            $remote_photos = false;
            $reviews = false;
            if($googlePlaceInfo)
            {
                //$googlePhotos = $restaurant->saveGooglePlacePhotos($googlePlaceInfo);
                $remote_photos = false; //$googlePhotos;
                $googleReviews = $restaurant->saveGooglePlaceReviews($googlePlaceInfo);
                $reviews = $googleReviews;
                
                $googleHours = $restaurant->saveGooglePlaceHours($googlePlaceInfo);
                $hours = $googleHours;
            }
            if($remote_photos || $reviews)
            {
                $restaurantInfo->is_parsed = true;
                $restaurantInfo->save();
                $restaurant->load(['votes']);
                $restaurant->restaurant = $restaurantInfo;
            }

        }
        
        foreach($restaurant->amenities as $item)
        {
            $amenitiesArray[$item->title][] = $item->item;
        }
        $restaurant->amenitiesArray = $amenitiesArray;
        
        return $restaurant;
    }

    /**
     * Update the specified restaurant in storage.
     *
     * @param  RestaurantUpdateRequest $request
     * @param  Spot $restaurant
     * @return Restaurant
     */
    public function update(RestaurantUpdateRequest $request, $restaurant)
    {
        $restaurant->update($request->except(['description']));
        $restaurant->description = $request->has('description') ? e($request->description) : '';
        $restaurant->save();

        return $restaurant;
    }

    /**
     * Remove the specified restaurant from storage.
     *
     * @param RestaurantDestroyRequest $request
     * @param Spot $restaurant
     * @return bool|null
     */
    public function destroy(RestaurantDestroyRequest $request, $restaurant)
    {
        return ['result' => $restaurant->delete()];
    }
    
    protected function checkUrl(string $url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
}
