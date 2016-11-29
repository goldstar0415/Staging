<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\Map\SpotListRequest;
use App\Http\Requests\Map\SpotsSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\Spot;
use App\SpotView;
use App\SpotPoint;
use App\SpotTypeCategory;
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
class MapController extends Controller {

    /**
     * Get spots in bounding box
     *
     * @deprecated Now all request sends to getSpots
     * @param MapSearchRequest $request
     * @return mixed
     */
    public function getSearch(MapSearchRequest $request) {
        return SpotPoint::getInBBoxes($request->get('b_boxes'))->get();
    }

    /**
     * Get weather by latitude longitude
     *
     * @param WeatherRequest $request
     * @param Forecast $forecast
     * @return array
     */
    public function getWeather(WeatherRequest $request, Forecast $forecast) {
        return $forecast->get($request->get('lat'), $request->get('lng'));
    }

    /**
     * Get spots by special filters
     * @param SpotsSearchRequest $request
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSpots(SpotsSearchRequest $request) {
        /**
         * @var $spots \Illuminate\Database\Query\Builder
         */
        $spots = SpotView::select(
                        'mv_spots_spot_points.*', 
                        DB::raw("split_part(trim(ST_AsText(mv_spots_spot_points.location)::text, 'POINT()'), ' ', 2)::float AS lat"), 
                        DB::raw("split_part(trim(ST_AsText(mv_spots_spot_points.location)::text, 'POINT()'), ' ', 1)::float AS lng"),
                        'spots.title',
                        'AVG(spot_votes.vote) AS rating',
                        'spot_points.address'
                )->where('is_private', false)->where('is_approved', true);

        if ($request->has('search_text')) {
            $spots->where('title_address', 'ilike', "%$request->search_text%");
        }

        if ($request->has('filter.category_ids')) {
            $spots->whereIn('spot_type_category_id', $request->filter['category_ids']);
        }

        if ($request->has('type')) {
            $spots->whereRaw("spot_type_category_id in (
				select id from spot_type_categories where spot_type_id in (select id from spot_types WHERE name = '{$request->type}'))");
        }

        if ($request->has('filter.start_date')) {
            $spots->where('start_date', '>=', $request->filter['start_date']);
        } else {
            if ($request->has('type') && in_array($request->type, ['event']) and ! $request->has('filter.end_date')) {
                $spots->where('end_date', '>', Carbon::now()->format('Y-m-d'));
            }
        }

        if ($request->has('filter.end_date')) {
            $spots->where('end_date', '<=', $request->filter['end_date'] . ' 23:59:59');
        }

        if ($request->has('filter.tags') && !empty($request->filter['tags'])) {
            $spots->joinWhere('tags', 'tags.name', 'in', $request->filter['tags']);
            $spots->join('spot_tag', function ($join) {
                $join->on('spot_tag.spot_id', '=', 'mv_spots_spot_points.id')->on('spot_tag.tag_id', '=', 'tags.id');
            });
        }

        if ($request->has('filter.rating')) {
            $spots->whereRaw("mv_spots_spot_points.id in (select qRating.spot_id from (select spot_votes.spot_id, avg(spot_votes.vote) OVER (PARTITION BY spot_id) as ratingAvg from spot_votes) qRating where ratingAvg > ?)", [$request->filter['rating']]);
        }

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
        
        $spots->join('spots', 'spots.id', '=', 'mv_spots_spot_points.id');
        $spots->join('spot_votes', 'spot_votes.spot_id', '=', 'mv_spots_spot_points.id');
        $spots->join('spot_points', 'spot_points.spot_id', '=', 'mv_spots_spot_points.id');

        if ($request->has('filter.path')) {
            $path = [];
            foreach ($request->filter['path'] as $p) {
                $path[] = "{$p['lng']} {$p['lat']}";
            }
            $spots->whereRaw("ST_Distance(ST_GeogFromText('LINESTRING(" . implode(",", $path) . ")'),mv_spots_spot_points.location::geography) < ?", [6000]);
        }
        // search spots
        $spotsArr = $spots->skip(0)->take(1000)->get();
        // cache cetegory icon URLs
        $cats = SpotTypeCategory::select("spot_type_categories.id")->with('type')->get();
        $iconsCache = [];
        $typesCache = [];
        foreach ($cats as $c) {
            $iconsCache[$c->id] = $c->icon_url;
            $typesCache[$c->id] = ($c->type)?$c->type->display_name:null;
        }
        $points = [];
        // fill spots
        foreach ($spotsArr as $spot) {
            $points[] = [
                'id' => $spot->spot_point_id,
                'spot_id' => $spot->id,
                'location' => [
                    'lat' => $spot->lat,
                    'lng' => $spot->lng
                ],
                'category_icon_url' => $iconsCache[$spot->spot_type_category_id],
                'category_name' => $typesCache[$spot->spot_type_category_id],
                ''
            ];
        }
        return $points;
    }

    /**
     * @param SpotListRequest $request
     * @return mixed
     */
    public function getList(SpotListRequest $request) {
        return Spot::whereIsPrivate(false)
                        ->whereIn('id', $request->ids)
                        ->with([
                            'photos',
                            'user',
                            'tags',
                            'comments',
                            'remotePhotos',
                            'restaurant',
                            'hotel'
                        ])
                        ->get();
    }

}
