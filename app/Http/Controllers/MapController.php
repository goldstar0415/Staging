<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\Map\SpotListRequest;
use App\Http\Requests\Map\SpotsSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\Spot;
use App\SpotPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
     * @deprecated Now all request sends to getSpots
     * @param MapSearchRequest $request
     * @return mixed
     */
    public function getSearch(MapSearchRequest $request)
    {
        return SpotPoint::getInBBoxes($request->get('b_boxes'))->get();
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
        /**
         * @var $spots \Illuminate\Database\Query\Builder
         */
        $spots = Spot::select('spots.*')->where('is_private', false);

        if ($request->has('search_text')) {
            $spots->join('spot_points', 'spots.id', '=', 'spot_points.spot_id');
            $spots->where(function ($query) use ($request) {
                $query->where('spots.title', 'ilike', "%$request->search_text%")
                    ->orWhere('spot_points.address', 'ilike', "%$request->search_text%");
            });
        }

        if ($request->has('filter.category_ids')) {
            $spots->join('spot_type_categories', 'spot_type_categories.id', '=', 'spots.spot_type_category_id')
                ->whereIn('spot_type_categories.id', $request->filter['category_ids']);
        }

        if ($request->has('type')) {
            if (!$request->has('filter.category_ids')) {
                $spots->join('spot_type_categories', 'spot_type_categories.id', '=', 'spots.spot_type_category_id');
            }
            $spots->join('spot_types', 'spot_type_categories.spot_type_id', '=', 'spot_types.id')
                ->where('spot_types.name', $request->type);
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
                $query->where('end_date', '<=', $request->filter['end_date'] . ' 23:59:59')->orWhereNull('end_date');
            });
        }

        if ($request->has('filter.tags')) {
            $spots->joinWhere('tags', 'tags.name', 'in', $request->filter['tags']);
            $spots->join('spot_tag', function ($join) {
                $join->on('spot_tag.spot_id', '=', 'spots.id')->on('spot_tag.tag_id', '=', 'tags.id');
            });
        }

        if ($request->has('filter.rating')) {
            $spots->addSelect('spot_votes.vote');
            $spots->join('spot_votes', 'spot_votes.spot_id', '=', 'spots.id');
            $spots->groupBy('spots.id', 'vote')->havingRaw('avg(vote) > ' . $request->filter['rating']);
        }

        if ($request->has('filter.b_boxes')) {
            if (!$request->has('search_text')) {
                $spots->join('spot_points', 'spots.id', '=', 'spot_points.spot_id');
            }

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
            $spots->whereRaw(implode(' OR ', $search_areas));
        }

        if ($spots->withoutNewest()->count() > 1000) {
            return abort(403, 'Too many points found');
        }

        $points = [];
        $spots->get()->each(function ($spot) use (&$points) {
            $points = array_merge($points, $spot->points->map(function (SpotPoint $point) use ($spot) {
                $point->setAttribute('category_icon_url', $spot->category->icon_url);
                return $point;
            })->all());
        });

        return $points;
    }

    /**
     * @param SpotListRequest $request
     * @return mixed
     */
    public function getList(SpotListRequest $request)
    {
        return Spot::whereIsPrivate(false)->whereIn('id', $request->ids)->get();
    }
}
