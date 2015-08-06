<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\SpotPoint;
use Illuminate\Http\Request;
use Nwidart\ForecastPhp\Forecast;

use App\Http\Requests;

class MapController extends Controller
{
    public function getSearch(MapSearchRequest $request)
    {
        return SpotPoint::getInBBoxes($request->get('b_boxes'));
    }

    public function getWeather(WeatherRequest $request, Forecast $forecast)
    {
        return $forecast->get($request->get('lat'), $request->get('lng'));
    }
}
