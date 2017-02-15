<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\Http\Requests\Map\SpotListRequest;
use App\Http\Requests\Map\SpotsSearchRequest;
use App\Http\Requests\WeatherRequest;
use App\Spot;
use App\SpotView;
use App\SpotPoint;
use App\SpotType;
use App\SpotTypeCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Nwidart\ForecastPhp\Forecast;
use Log;
use DB;
use Cache;
use App\Http\Controllers\Event;
use App\Http\Requests;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class MapController
 * @package App\Http\Controllers
 *
 * Map controller
 */
class MapController extends Controller {

    public $rates_api_key = 'e13e6e6d8012ba2865114e215896980b';
    public $rates = null;
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
    public function getWeather( Request $request) {
        $url = $request->get('q', null);
        if(!empty($url) && filter_var($url, FILTER_VALIDATE_URL))
        {
            $client = new Client();
            try
            {
                $response = $client->get($url);
                $responseArray = json_decode($response->getBody()->getContents(), true);
                return $responseArray;
            }
            catch(RequestException $e) {}
        }
        return [];
        
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
                        'spots.avg_rating',
                        'spot_points.address',
                        'spots.minrate',
                        'spots.maxrate',
                        'spots.currencycode',
                        'spots.avg_rating',
                        'spots.total_reviews'
                )
                ->distinct()
                ->where('mv_spots_spot_points.is_private', false)
                ->where('mv_spots_spot_points.is_approved', true);
        $spots->leftJoin('spots', 'spots.id', '=', 'mv_spots_spot_points.id');
        if ($request->has('search_text')) {
            $spots->where('mv_spots_spot_points.title_address', 'ilike', "%$request->search_text%");
        }

        if ($request->has('filter.category_ids')) {
            $spots->whereIn('mv_spots_spot_points.spot_type_category_id', $request->filter['category_ids']);
        }

        if ($request->has('type')) {
            $spots->whereRaw("mv_spots_spot_points.spot_type_category_id in (
				select id from spot_type_categories where spot_type_id in (select id from spot_types WHERE name = '{$request->type}'))");
        }

        if ($request->has('filter.start_date')) {
            $spots->where('mv_spots_spot_points.start_date', '>=', $request->filter['start_date']);
        } else {
            if ($request->has('type') && in_array($request->type, ['event']) and ! $request->has('filter.end_date')) {
                $spots->where('mv_spots_spot_points.end_date', '>', Carbon::now()->format('Y-m-d'));
            }
        }

        if ($request->has('filter.end_date')) {
            $spots->where('mv_spots_spot_points.end_date', '<=', $request->filter['end_date'] . ' 23:59:59');
        }

        if ($request->has('filter.tags') && !empty($request->filter['tags'])) {
            
            $tags = $request->filter['tags'];
            $spots->join('tags', function($query) use ($tags){
                $query->where('tags.name', 'in', $tags);
                foreach($tags as $tag)
                {
                    $query->orWhere('spots.title', 'ilike', DB::raw('%'.$tag.'%'));
                }
            });
            $spots->join('spot_tag', function ($join) {
                $join->on('spot_tag.spot_id', '=', 'mv_spots_spot_points.id')
                     ->on('spot_tag.tag_id', '=', 'tags.id');
            });
            
        }

        if ($request->has('filter.rating')) {
            //$spots->whereRaw("mv_spots_spot_points.id in (select qRating.spot_id from (select spot_votes.spot_id, avg(spot_votes.vote) OVER (PARTITION BY spot_id) as ratingAvg from spot_votes) qRating where ratingAvg > ?)", [$request->filter['rating']]);
            if($request->filter['rating'] > 0)
            {
                $spots->where('spots.avg_rating', '>=', $request->filter['rating']);
            }
        }
        $calc_cur = [];
        if ($request->has('filter.price')) 
        {
            $price = $request->filter['price'];
            $this->getRates();
            $rates = $this->rates;
            if(empty($rates))
            {
                $spots->where('spots.minrate', '<=', $price)->where('spots.currencycode', 'USD');
            }
            else
            {
                $spots->where(function($query) use ($price, $rates, &$calc_cur){
                    $query->where(function($subquery) use ($price, &$calc_cur)
                    {
                        $calc_cur['USD'] = $price;
                        $subquery->whereRaw('CAST (spots.minrate AS FLOAT) <= ?', [(float)$price])->where('spots.currencycode', 'USD');
                    });
                    foreach($rates as $cc => $cr)
                    {
                        $query->orWhere(function($subquery) use ($price, $cc, $cr, &$calc_cur){
                            $calc_cur[$cc] = $price * $cr;
                            $subquery->whereRaw('CAST (spots.minrate AS FLOAT) <= ?', [$price * $cr])->where('spots.currencycode', $cc);
                        });
                    }
                });
            }
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
        
        $spots->leftJoin('spot_points', 'spot_points.spot_id', '=', 'mv_spots_spot_points.id');
        $spots->with('rating');
        
        if ($request->has('filter.path')) {
            $path = [];
            foreach ($request->filter['path'] as $p) {
                $path[] = "{$p['lng']} {$p['lat']}";
            }
            $spots->whereRaw("ST_Distance(ST_GeogFromText('LINESTRING(" . implode(",", $path) . ")'),mv_spots_spot_points.location::geography) < ?", [6000]);
        }
        // search spots
        //var_dump($spots->toSql());
        //dd($spots->getBindings());
        $spotsArr = $spots->skip(0)->take(1000)->get();
        // cache cetegory icon URLs
        $cats = SpotTypeCategory::select("spot_type_categories.id", "spot_type_categories.spot_type_id")->get();
        $iconsCache = [];
        $typesCache = [];
        foreach ($cats as $c) {
            $iconsCache[$c->id] = $c->icon_url;
            $typesCache[$c->id]['display_name'] = ($c->type)?$c->type->display_name:null;
            $typesCache[$c->id]['name'] = ($c->type)?$c->type->name:null;
        }
        $points = [];
        // fill spots
        foreach ($spotsArr as $spot) {
            
            //$rating = (isset($spot->rating[0])) ? (float)$spot->rating[0]->rating : 0;
            //if(empty($rating) && Cache::has('spot-ratings-' . $spot->id))
            //{
            //    $ratingsArr = Cache::get('spot-ratings-' . $spot->id);
            //    $rating = $ratingsArr['total']['rating'];
            //}
            $points[] = [
                'id' => $spot->spot_point_id,
                'spot_id' => $spot->id,
                'location' => [
                    'lat' => $spot->lat,
                    'lng' => $spot->lng
                ],
                'rating' => $spot->rating,
                'title' => $spot->title,
                'address' => $spot->address,
                'category_icon_url' => $iconsCache[$spot->spot_type_category_id],
                'category_name' => $typesCache[$spot->spot_type_category_id]['display_name'],
                'type' => $typesCache[$spot->spot_type_category_id]['name'],
                'minrate' => $spot->minrate,
                'maxrate' => $spot->maxrate,
                'currencycode' => $spot->currencycode,
                'cover_url'    => $spot->cover,
                'avg_rating' => $spot->avg_rating, 
                'total_reviews' => $spot->total_reviews,
            ];
        }
        return $points;
    }
    
    public function getRates()
    {
        $rates = Cache::get('currency_rates');
        if(empty($rates))
        {
            $client = new Client();
            try
            {
                $response = $client->get('http://www.apilayer.net/api/live?access_key=' . $this->rates_api_key);
                $rates =  json_decode($response->getBody()->getContents(), true);
                if(isset($rates['quotes']))
                {
                    $calcRates = $this->calcRates($rates['quotes']);
                    Cache::put('currency_rates', $calcRates , Carbon::now()->addDay());
                    $this->rates = $calcRates;
                }
            }
            catch (Exception $e){  }
        }
        else
        {
            $this->rates = $rates;
        }
        return $this->rates;
    }
    
    public function calcRates($arr)
    {
        $calcRates = [];
        foreach($arr as $cur => $rate)
        {
            $subCur = substr($cur, -3);
            if($subCur != 'USD')
            {
                $calcRates[$subCur] = $rate;
            }
        }
        return $calcRates;
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
                        ])
                        ->get();
    }

}
