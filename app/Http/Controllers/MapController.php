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
     * @deprecated
     *
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
                        'spots_mat_view.*',
                        DB::raw("split_part(trim(ST_AsText(spots_mat_view.location)::text, 'POINT()'), ' ', 2)::float AS lat"),
                        DB::raw("split_part(trim(ST_AsText(spots_mat_view.location)::text, 'POINT()'), ' ', 1)::float AS lng")
                )
                //->distinct()
                ->where('spots_mat_view.is_private', false);

        $is_approved = true;
        if($request->has('filter.is_approved') && auth()->check() && auth()->user()->hasRole('admin'))
        {
            $is_approved = $request->filter['is_approved'];
        }
        if($is_approved !== 'any')
        {
            $spots->where('spots_mat_view.is_approved', filter_var($is_approved, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->has('search_text')) {
            $spots->whereRaw("concat_ws(' ', spots_mat_view.title::text, spots_mat_view.address::text) ilike ?", "%$request->search_text%");
        }

        if ($request->has('filter.category_ids')) {
            $spots->whereIn('spots_mat_view.spot_type_category_id', $request->filter['category_ids']);
        }

        if ($request->has('type')) {
            $spots->where("spots_mat_view.type_name", $request->type);
        }

        if ($request->has('filter.start_date')) {
            $spots->where('spots_mat_view.start_date', '>=', $request->filter['start_date']);
        } else {
            if ($request->has('type') && $request->type == 'event' and ! $request->has('filter.end_date')) {
                $spots->where('spots_mat_view.end_date', '>', Carbon::now()->format('Y-m-d'));
            }
        }
        if ($request->has('filter.end_date')) {
            $spots->where('spots_mat_view.end_date', '<=', $request->filter['end_date'] . ' 23:59:59');
        }

        if ($request->has('filter.tags') && !empty($request->filter['tags'])) {
            
            $tags = $request->filter['tags'];
            
            $spots->leftJoin('spot_tag', function ($join) {
                $join->on('spot_tag.spot_id', '=', 'spots_mat_view.id');
            });
            $spots->leftJoin('tags', function($join) {
                $join->on('spot_tag.tag_id', '=', 'tags.id');
            });
            
            $spots->where(function($query) use ($tags){
                $query->whereIn('tags.name', $tags);
                foreach($tags as $tag)
                {
                    $query->orWhere('spots_mat_view.title', 'ilike', "%$tag%");
                }
            });
        }

        if ($request->has('filter.rating')) {
            //$spots->whereRaw("spots_mat_view.id in (select qRating.spot_id from (select spot_votes.spot_id, avg(spot_votes.vote) OVER (PARTITION BY spot_id) as ratingAvg from spot_votes) qRating where ratingAvg > ?)", [$request->filter['rating']]);
            if($request->filter['rating'] > 0)
            {
                $spots->where('spots_mat_view.avg_rating', '>=', $request->filter['rating']);
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
                $spots->where('spots_mat_view.minrate', '<=', $price)->where('spots_mat_view.currencycode', 'USD');
            }
            else
            {
                $spots->where(function($query) use ($price, $rates, &$calc_cur){
                    $query->where(function($subquery) use ($price, &$calc_cur)
                    {
                        $calc_cur['USD'] = $price;
                        $subquery->whereRaw('CAST (spots_mat_view.minrate AS FLOAT) <= ?', [(float)$price])->where('spots_mat_view.currencycode', 'USD');
                    });
                    foreach($rates as $cc => $cr)
                    {
                        $query->orWhere(function($subquery) use ($price, $cc, $cr, &$calc_cur){
                            $calc_cur[$cc] = $price * $cr;
                            $subquery->whereRaw('CAST (spots_mat_view.minrate AS FLOAT) <= ?', [$price * $cr])->where('spots_mat_view.currencycode', $cc);
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
                            '"spots_mat_view"."location" && ST_MakeEnvelope(%s, %s, %s, %s, 4326)',
                            $b_box['_southWest']['lng'], 
                            $b_box['_southWest']['lat'], 
                            $b_box['_northEast']['lng'], 
                            $b_box['_northEast']['lat']
                    );
                }
                $spots->whereRaw(implode(' OR ', $search_areas));
            }
        }

        if ($request->has('filter.path')) {
            $path = [];
            foreach ($request->filter['path'] as $p) {
                $path[] = "{$p['lng']} {$p['lat']}";
            }
            $spots->whereRaw("ST_Distance(ST_GeogFromText('LINESTRING(" . implode(",", $path) . ")'),spots_mat_view.location::geography) < ?", [6000]);
        }
        // search spots
        $spotsArr = $spots->skip(0)->take(1000)->get();
        // cache cetegory icon URLs
        $cats = SpotTypeCategory::select("spot_type_categories.id", "spot_type_categories.spot_type_id")->get();
        $iconsCache = [];
        foreach ($cats as $c) {
            $iconsCache[$c->id] = $c->icon_url;
        }
        $points = [];
        // fill spots
        $idsArr = [];
        foreach ($spotsArr as $spot) {
            if(in_array($spot->id, $idsArr))
            {
                continue;
            }
            $cover = env('APP_URL') . '/uploads/missings/cover/original/missing.png';
            if(!empty(trim($spot->remote_cover)))
            {
                $cover = trim($spot->remote_cover);
            }
            if(!empty(trim($spot->cover)))
            {
                $cover = env('S3_ENDPOINT') . "/cover/". $spot->id . "/original/" . trim($spot->cover);
            }

            $points[] = [
                'id' => $spot->spot_point_id,
                'spot_id' => $spot->id,
                'location' => [
                    'lat' => $spot->lat,
                    'lng' => $spot->lng
                ],
                'title' => $spot->title,
                'address' => $spot->address,
                'category_icon_url' => $iconsCache[$spot->spot_type_category_id],
                'category_name' => $spot->type_display_name,
                'type' => $spot->type_name,
                'minrate' => $spot->minrate,
                'maxrate' => $spot->maxrate,
                'currencycode' => $spot->currencycode,
                'cover_url'    => $cover, //$spot->cover,
                'avg_rating' => $spot->avg_rating, 
                'total_reviews' => $spot->total_reviews,
                'is_approved' => $spot->is_approved,
                'is_private' => $spot->is_private,
                'start_date' => $spot->start_date,
            ]; 
            $idsArr[] = $spot->id;
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
