<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\Map\SpotsSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\Spot;
use App\SpotPoint;
use Carbon\Carbon;
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
    public function getSearch(MapSearchRequest $request)
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

    /**
     * Get spots by special filters
     * @param SpotsSearchRequest $request
     */
    public function getSpots(SpotsSearchRequest $request)
    {
        $spots = Spot::query();

        if ($request->has('search_text')) {
            $spots->where('title', 'ilike', "%$request->search_text%");
            $spots->orWhereHas('points', function ($query) use ($request) {
                $query->where('address', 'ilike', "%$request->search_text%");
            });
        }

        if ($request->has('start_date')) {
            $spots->where('start_date', '>=', $request->start_date);
        } else {
            $spots->where('start_date', '>=', Carbon::now()->format('Y-m-d'));
        }

        if ($request->has('end_date')) {
            $spots->where('end_date', '=>', $request->end_date);
        }

        if ($request->has('category')) {
            $spots->whereHas('category', function ($query) use ($request) {
                $query->whereIn('id', $request->category_ids);
            });
        }

        if ($request->has('rating')) {

        }
    }
}
