<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\Map\SpotListRequest;
use App\Http\Requests\Map\SpotsSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\Spot;
use App\SpotView;
use App\SpotPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Nwidart\ForecastPhp\Forecast;
use Log;
use DB;
use App\Http\Controllers\Event;
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
        $spots = SpotView::select(
			'mv_spots_spot_points.*',
			DB::raw("split_part(trim(ST_AsText(mv_spots_spot_points.location)::text, 'POINT()'), ' ', 2)::float AS lat"),
			DB::raw("split_part(trim(ST_AsText(mv_spots_spot_points.location)::text, 'POINT()'), ' ', 1)::float AS lng")
		)->where('is_private', false);
		/** @todo search text */
        /*if ($request->has('search_text')) {
            $spots->where(function ($query) use ($request) {
                $query->where('spots.title', 'ilike', "%$request->search_text%")
                    ->orWhereRaw('spots.id in (select spot_id from spot_points where address ilike ?)', ["%$request->search_text%"]);
            });
        }
		/** @todo catgory ids */
        /*if ($request->has('filter.category_ids')) {
            $spots->join('spot_type_categories', 'spot_type_categories.id', '=', 'spots.spot_type_category_id')
                ->whereIn('spot_type_categories.id', $request->filter['category_ids']);
        }*/

        if ($request->has('type')) {
			$spots->whereRaw("spot_type_category_id in (
				select id from spot_type_categories where spot_type_id in (select id from spot_types WHERE name = '{$request->type}'))");
        }

		if ($request->has('filter.start_date')) {
			$spots->where('start_date', '>=', $request->filter['start_date']);
		} else {
			if ($request->has('type') && in_array($request->type, ['event'])) {
				$spots->where('start_date', '>=', Carbon::now()->format('Y-m-d'));
			}
		}

        if ($request->has('filter.end_date')) {
            $spots->where('end_date', '<=', $request->filter['end_date'] . ' 23:59:59');
        }
		/** @todo tags */
        /*if ($request->has('filter.tags')) {
            $spots->joinWhere('tags', 'tags.name', 'in', $request->filter['tags']);
            $spots->join('spot_tag', function ($join) {
                $join->on('spot_tag.spot_id', '=', 'spots.id')->on('spot_tag.tag_id', '=', 'tags.id');
            });
        }*/
		/** @todo rating */
        /*if ($request->has('filter.rating')) {
            $spots->addSelect('spot_votes.vote');
            $spots->join('spot_votes', 'spot_votes.spot_id', '=', 'spots.id');
            $spots->groupBy('spots.id', 'vote')->havingRaw('avg(vote) > ' . $request->filter['rating']);
        }*/

        if ($request->has('filter.b_boxes')) {
            if (!$request->has('search_text')) {
				$search_areas = [];
				foreach ($request->filter['b_boxes'] as $b_box) {
					$search_areas[] = sprintf(
						'"mv_spots_spot_points"."location" && ST_MakeEnvelope(%s, %s, %s, %s, 4326)',
						$b_box['_southWest']['lng'],
						$b_box['_southWest']['lat'],
						$b_box['_northEast']['lng'],
						$b_box['_northEast']['lat']
					);
				}
				$spots->whereRaw(implode(' OR ', $search_areas));
			}
        }
		// Display all SQL executed in Eloquent

		/** @todo icon_url */
        $points = [];
		//Log::debug($spots);
		Log::debug($spots->skip(0)->take(1000)->toSql());
		$iconUrlCache = [];
		$tt = microtime(1);
        $spotsArr = $spots->skip(0)->take(1000)->get();
		foreach($spotsArr as $spot) {
			$points[] = [
				'id'		=> $spot->spot_point_id,
				'spot_id'	=> $spot->id,
				'location' => [
					'lat' => $spot->lat,
					'lng' => $spot->lng
				],
				'category_icon_url' => "http://zoomtivity.wirnex.com/uploads/missings/icons/original/missing.png",
				//'address'			=> $spot->address
			];
		}
				//each(function ($spot) use (&$points, &$iconUrlCache) {
			//Log::debug((array)$spot);
            //$points = array_merge($points, $spot->points->map(function (SpotPoint $point) use ($spot, &$iconUrlCache) {
				/*if (!array_key_exists($spot->spot_type_category_id, $iconUrlCache)) {
					$iconUrlCache[$spot->spot_type_category_id] = $spot->category->icon_url;
					Log::debug('cache miss '.$spot->spot_type_category_id);
				}
                $point->setAttribute('category_icon_url', $iconUrlCache[$spot->spot_type_category_id]);*/
			//	unset($point->address);
//                return $point;
  //          })->all());
        //});
		Log::debug(microtime(1) - $tt);
		Log::debug("");
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
