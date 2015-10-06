<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\SpotPoint;
use Nwidart\ForecastPhp\Forecast;

use App\Http\Requests;

/**
 * Class MapController
 * @package App\Http\Controllers
 *
 * Map controller
 */
class MapController extends Controller
{
    /**
     * Get spots in bounding box
     *
     * @param MapSearchRequest $request
     * @return mixed
     */
    public function getSearch(MapSearchRequest $request)//TODO: change to GET method
    {
        return SpotPoint::getInBBoxes($request->get('b_boxes'));
    }

    /**
     * Get weather by latitude longitude
     *
     * @param WeatherRequest $request
     * @param Forecast $forecast
     * @return array
     */
    public function getWeather(WeatherRequest $request, Forecast $forecast)
    {
        return $forecast->get($request->get('lat'), $request->get('lng'));
    }
}
