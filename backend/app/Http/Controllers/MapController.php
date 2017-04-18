<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\SpotListRequest;
use App\Http\Requests\Map\TextualSearchRequest;
use App\Http\Requests\Map\RadiusSelectionRequest;
use App\Http\Requests\Map\LassoSelectionRequest;
use App\Http\Requests\Map\PathSelectionRequest;
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
use App\Services\SqlEscape;

/**
 * Class MapController
 * @package App\Http\Controllers
 *
 * Map controller
 */
class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:60,1', ['only' => [
            'getSearch',
            'getSpotsRadiusSelection',
            'getSpotsLassoSelection',
            'getSpotsPathSelection',
        ]]);
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
    public function getWeather(Request $request) {
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

    protected function grabSpotViews() {
        $spots = SpotView::select(
            'spots_mat_view.spot_point_id',
            'spots_mat_view.id',
            'spots_mat_view.title',
            'spots_mat_view.address',
            'spots_mat_view.spot_type_category_id',
            'spots_mat_view.type_display_name',
            'spots_mat_view.type_name',
            'spots_mat_view.minrate',
            'spots_mat_view.maxrate',
            'spots_mat_view.currencycode',
            'spots_mat_view.avg_rating',
            'spots_mat_view.total_reviews',
            'spots_mat_view.is_approved',
            'spots_mat_view.is_private',
            'spots_mat_view.start_date',
            'spots_mat_view.user_id',
            'spots_mat_view.remote_cover',
            'spots_mat_view.cover',
            DB::raw("ST_Y(spots_mat_view.location::geometry)::float AS lat"),
            DB::raw("ST_X(spots_mat_view.location::geometry)::float AS lng")
        )
        ->where('spots_mat_view.is_private', false);

        return $spots;
    }

    /**
     * Apply custom paginator for spot list
     *
     * @param \App\Http\Requests\Request $request
     * @param \Illuminate\Database\Eloquent\Builder &$spots
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function paginateSpots(
        \App\Http\Requests\Request $request,
        \Illuminate\Database\Eloquent\Builder &$spots
    ) {
        $spots = $spots->skip($request->input('pagination_offset', 0))->take(1000)->get();
    }

    /**
     * Apply search filters to reduce dataset
     *
     * @param \App\Http\Requests\Request $request
     * @param \Illuminate\Database\Eloquent\Builder &$spots
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function filterSpotViews(
        \App\Http\Requests\Request $request,
        \Illuminate\Database\Eloquent\Builder &$spots
    ) {
        if ($request->has('filter.type')) {
            $spots->where("spots_mat_view.type_name", $request->filter['type']);
        }

        $is_approved = true; // todo: this should be refactored
        // maybe admin shouldnt set this if they want "any"?
        if (   $request->has('filter.is_approved')
            && auth()->check()
            && auth()->user()->hasRole('admin')
        ) {
            $is_approved = $request->filter['is_approved'];
        }

        if ($is_approved !== 'any') {
            $spots->where('spots_mat_view.is_approved', $is_approved);
        }

        if ($request->has('filter.category_ids')) {
            $spots->whereIn(
                'spots_mat_view.spot_type_category_id',
                $request->filter['category_ids']);
        }

        if ($request->has('filter.start_date')) {
            $spots->where('spots_mat_view.start_date', '>=', Carbon::parse($request->filter['start_date'])->toDateTimeString());
        } else {
            
            if ($request->has('filter.type') && $request->filter['type'] == 'event') {
                $spots->where('spots_mat_view.start_date', '>', Carbon::now()->toDateTimeString());
            }
        }
        if ($request->has('filter.end_date')) {
            $spots->where('spots_mat_view.end_date', '<=', Carbon::parse($request->filter['end_date'])->endOfDay()->toDateTimeString());
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

                foreach($tags as $tag) {
                    $query->orWhere('spots_mat_view.title', 'ilike', "%$tag%");
                }
            });
        }

        if ($request->has('filter.rating') && $request->filter['rating'] > 0) {
            $spots->where('spots_mat_view.avg_rating', '>=', $request->filter['rating']);
        }

        if ($request->has('filter.price')) {
            $spots
                ->where(DB::raw('spots_mat_view.minrate::float'), '<=', (float)$request->filter['price']);
        }
    }

    /**
     * Hydrate points from spot views
     *
     * @param Illuminate\Database\Eloquent\Collection &$spots
     *
     * @return array
     */
    protected function makePointsFromSpotViews(\Illuminate\Database\Eloquent\Collection $spots) {
        $icons_cache = SpotTypeCategory::getIconCache();
        $points = [];

        $id_already_seen = [];
        foreach ($spots as $spot) {
            if(array_key_exists($spot->id, $id_already_seen)) {
                continue;
            }

            $cover = env('APP_URL') . '/uploads/missings/cover/original/missing.png';
            
            $trimmed_remote_cover = trim($spot->remote_cover);
            if(!empty($trimmed_remote_cover)) {
                $cover = $trimmed_remote_cover;
            }

            $trimmed_cover = trim($spot->cover);
            if(!empty($trimmed_cover)) {
                $cover = env('S3_ENDPOINT') . "/cover/". $spot->id . "/original/" . trim($spot->cover);
            }

            $category_icon_url = array_key_exists($spot->spot_type_category_id, $icons_cache)
                ? $icons_cache[$spot->spot_type_category_id]
                : null;

            $points[] = [
                'id'                => $spot->spot_point_id,
                'spot_id'           => $spot->id,
                'location'          => ['lat' => (double) $spot->lat, 'lng' => (double) $spot->lng],
                'category_icon_url' => $category_icon_url,
                'title'             => $spot->title,
                'address'           => $spot->address,
                'category_name'     => $spot->type_display_name,
                'type'              => $spot->type_name,
                'minrate'           => $spot->minrate,
                'maxrate'           => $spot->maxrate,
                'currencycode'      => $spot->currencycode,
                'cover_url'         => $cover,
                'avg_rating'        => $spot->avg_rating, 
                'total_reviews'     => $spot->total_reviews,
                'is_approved'       => $spot->is_approved,
                'is_private'        => $spot->is_private,
                'start_date'        => $spot->start_date,
                'user_id'           => $spot->user_id,
            ]; 

            $id_already_seen[$spot->id] = true;
        }

        return $points;
    }

    /**
     * Get spots by textual search
     * @param \App\Http\Requests\Map\TextualSearchRequest $request
     *
     * @return array
     */
    public function getSearch(TextualSearchRequest $request) {
        $spots = $this->grabSpotViews();
        $this->filterSpotViews($request, $spots);

        $spots->where(DB::raw("spots_mat_view.title_address::text"), "ilike", "%{$request->input('query')}%");

        if ($request->has('lat') && $request->has('lng')) {
            $spots->orderBy(DB::raw("ST_Distance(
                ST_GeogFromText('POINT( {$request->getPoint()} )'),
                spots_mat_view.location::geography
            )"), 'asc');
        }

        $this->paginateSpots($request, $spots);
        return $this->makePointsFromSpotViews($spots);
    }

    /**
     * Get spots by radius selection
     * @param \App\Http\Requests\Map\RadiusSelectionRequest $request
     *
     * @return array
     */
    public function postSpotsRadiusSelection(RadiusSelectionRequest $request) {
        $spots = $this->grabSpotViews();
        $this->filterSpotViews($request, $spots);

        // due to a bug in PDO (https://github.com/laravel/framework/issues/9390)
        // we need to do this poorly
        $spots->whereRaw("ST_DWithin(
            ST_GeogFromText('POINT( {$request->getPoint()} )'),
            spots_mat_view.location::geography,
            ?
        )", [
            $request->input('radius')
        ]);

        $this->paginateSpots($request, $spots);
        return $this->makePointsFromSpotViews($spots);
    }

    /**
     * Get spots by lasso selection
     * @param \App\Http\Requests\Map\LassoSelectionRequest $request
     *
     * @return array
     */
    public function postSpotsLassoSelection(LassoSelectionRequest $request) {
        $spots = $this->grabSpotViews();
        $this->filterSpotViews($request, $spots);

        $spots->whereRaw("ST_Intersects(
            spots_mat_view.location::geography,
            ST_MakePolygon(
                ST_GeomFromText('LINESTRING( {$request->getClosedLineString()} )')
            )::geography
        )");


        $this->paginateSpots($request, $spots);
        return $this->makePointsFromSpotViews($spots);
    }

    /**
     * Get spots by path selection
     * @param \App\Http\Requests\Map\PathSelectionRequest $request
     *
     * @return array
     */
    public function postSpotsPathSelection(PathSelectionRequest $request) {
        $spots = $this->grabSpotViews();
        $this->filterSpotViews($request, $spots);

        $spots->whereRaw("ST_DWithin(
            ST_GeogFromText('LINESTRING( {$request->getLineString()} )')::geography,
            spots_mat_view.location::geography,
            ?
        )", [
            $request->input('buffer')
        ]);
        
        $this->paginateSpots($request, $spots);
        return $this->makePointsFromSpotViews($spots);
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
