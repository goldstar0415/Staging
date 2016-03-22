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
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSpots(SpotsSearchRequest $request)
    {
        $spots = Spot::where('is_private', false);

        if ($request->has('search_text')) {
            $spots->where(function ($query) use ($request) {
                $query->where('title', 'ilike', "%$request->search_text%")
                    ->orWhereHas('points', function ($query) use ($request) {
                    $query->where('address', 'ilike', "%$request->search_text%");
                });
            });
        }

        if ($request->has('type')) {
            $spots->whereHas('category.type', function ($query) use ($request) {
                $query->where('name', $request->type);
            });
        }

        if ($request->has('filter.start_date')) {
            $spots->where(function ($query) use ($request) {
                $query->where('start_date', '>=', $request->filter['start_date'])->orWhereNull('start_date');
            });
        } else {
            $spots->where(function ($query) use ($request) {
                $query->where('start_date', '>=', Carbon::now()->format('Y-m-d'))->orWhereNull('start_date');
            });
        }

        if ($request->has('filter.end_date')) {
            $spots->where(function ($query) use ($request) {
                $query->where('end_date', '<=', $request->filter['end_date'])->orWhereNull('end_date');
            });
        }

        if ($request->has('filter.category_ids')) {
            $spots->whereHas('category', function ($query) use ($request) {
                $query->whereIn('id', $request->filter['category_ids']);
            });
        }

        if ($request->has('filter.tags')) {
            $spots->whereHas('tags', function ($query) use ($request) {
                $query->whereIn('name', $request->filter['tags']);
            });
        }

        if ($request->has('filter.rating')) {
            $spots->whereHas('votes', function ($query) {
                $query->select(\DB::raw("avg(vote) as avg_vote"));
            }, '>=', $request->filter['rating']);
        }

        if ($request->has('filter.b_boxes')) {
            $spots->whereHas('points', function ($query) use ($request) {
                $search_areas = [];
                foreach ($request->filter['b_boxes'] as $b_box) {
                    $search_areas[] = sprintf(
                        '"location" && ST_MakeEnvelope(%s, %s, %s, %s, 4326)',
                        $b_box['_southWest']['lng'],
                        $b_box['_southWest']['lat'],
                        $b_box['_northEast']['lng'],
                        $b_box['_northEast']['lat']
                    );
                }
                $query->whereRaw(implode(' OR ', $search_areas));
            });
        }

        $points = [];
        $spots->get()->each(function ($spot) use (&$points) {
            return $spot->points->each(function ($point) use ($spot, &$points) {
                $points[] = $point->setRelation('spot', $spot->setRelations([
                    'category' => $spot->category->setRelation('type', $spot->category->type)
                ]));
            });
        });

        return $points;
    }
}
