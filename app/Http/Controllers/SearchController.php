<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 19.10.2016
 * Time: 22:15
 */

namespace App\Http\Controllers;

use App\SpotTitleUnique;
use Illuminate\Http\Request;
use App\SpotView;
use DB;
use Log;

class SearchController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request) {
        $query  = $request->has('query') ? $request->get('query') : null;
        $lat    = $request->has('lat') ? $request->get('lat') : null;
        $lng    = $request->has('lng') ? $request->get('lng') : null;
        $q = (object)[];
        $q->minimumSimilarity = 0.0;
        $q->words = explode(" ", $query);
        $q->weightQueryArr = [];
        //
        // put query words into to_tsquery format with A weight (A is for the spot title)
        //
        foreach ($q->words as &$w) {
            $w = preg_replace("/\'/","\"", $w);
            $q->weightQueryArr[] = "''{$w}:*A''";
        }
        // this would produce string like "''horn:*A'' | ''pizza:*A''"
        // which means search by OR in the title (A weight), * means words beginning with horn or pizza
        $q->toTsQuery = implode(' | ', $q->weightQueryArr);

        $debug = [$q->weightQueryArr];

        $selectArr = [
            'mv_spots_spot_points.id',
            'spots.title as value',
            DB::raw("ts_rank_cd(mv_spots_spot_points.fts, to_tsquery('{$q->toTsQuery}'), 32) as rank ")
        ];

        if ($lat && $lng) {
            $selectArr[] = DB::raw("ST_Distance(
                ST_GeogFromText('SRID=4326;POINT({$lng} {$lat})'),
                mv_spots_spot_points.location) as dist");
        }

        $spots = SpotView::select($selectArr);

        $spots->whereRaw("mv_spots_spot_points.fts @@ to_tsquery('{$q->toTsQuery}')");
        $spots->orderBy('rank', 'desc');
        if ($lat && $lng) {
            $spots->orderBy('dist', 'asc');
        }
        $spots->join('spots', function ($join) {
           $join->on('mv_spots_spot_points.id', '=', 'spots.id');
        });
        $spotsFound = $spots->skip(0)->take(20)->get();

        Log::debug("spotsFound: ".get_class($spotsFound));

        $this->http = new \GuzzleHttp\Client;

        $response = $this->http->get("https://maps.googleapis.com/maps/api/place/autocomplete/json", [
            'query' => [
                'input' => $query,
                'key'   => 'AIzaSyCaT8bBy2zIa_hEwADEBRTLVM8f1dLDFw0',
                'types' => 'geocode'
            ]
        ]);

        $data = json_decode((string)$response->getBody(), true);
        $locationSuggestions = [];
        if ($data && array_key_exists("predictions", $data) && is_array($data['predictions'])) {
            $first = true;
            foreach ($data['predictions'] as $p) {
                $locationSuggestions[] = [
                    'value'     => $p['description'],
                    'type'      => 'location',
                    'first'     => $first,
                    'place_id'  => $p['place_id']
                ];
                $first = false;
            }
        }

        //
        // suggestions
        //
        /*$suggestionCount = 10;
        $q->similar = [];
        $allFound = true;
        foreach ($q->words as $wordNum => $w) {
            $spotTitleUnique = SpotTitleUnique::select('word')
                ->selectRaw("similarity(word, '{$w}') as sml")
                ->whereRaw("word % '{$w}'")
                ->orderBy('sml', 'desc')
                ->take($suggestionCount)->get();
            $first          = null;
            $lastOkSimilar  = null;
            $lastOkSimilarW = null;
            if (count($spotTitleUnique) < 10) {
                
            }
            foreach ($spotTitleUnique as $index => $s) {
                if (!array_key_exists($index, $q->similar)) {
                    $q->similar[$index] = "";
                }
                if ($index === 0) {
                    if ((int)$s->sml === 1) {
                        $first = $s->word;
                    } else {
                        $allFound = false;
                    }
                }
                if ($s->sml >= $q->minimumSimilarity) {
                    $lastOkSimilar  = $s->word;
                    $lastOkSimilarW = $s->sml;
                }
                if ($first) {
                    // use first suggestion with 1 for all
                    $word   = $first;
                    $weight = 1;
                } else if ($s->sml < $q->minimumSimilarity && $lastOkSimilar) {
                    // show last good suggestion
                    $word   = $lastOkSimilar;
                    $weight = $lastOkSimilarW;
                } else {
                    // sadly being here
                    $word   = $s->word;
                    $weight = $s->sml;
                }

                //$q->similar[$index] = $q->similar[$index] . " {$word}({$weight})";
                $q->similar[$index] = $q->similar[$index] . " {$word}";
            }
        }/**/
        $maxSpotsNumber = count($locationSuggestions) >= 5 ? 5 : 10 - count($locationSuggestions);
        $spotsAr = [];
        $first = true;
        foreach($spotsFound as $s) {
            $spotsAr[] = [
                'value'     => $s->value,
                'type'      => 'spot',
                'first'     => $first,
                'spotId'    => $s->id,
                'dist'      => ($lat && $lng) ? (double)$s->dist : 0
            ];
            $first = false;
            $maxSpotsNumber--;
            if ($maxSpotsNumber === 0) {
                break;
            }
        }
        // siggestions are not being used for now, could be used when no spots found
        // when needed the migration has to be applied
        // CREATE MATERIALIZED VIEW mv_spots_unique_title as
        //     select word from ts_stat('select to_tsvector(''simple'', title) from spots');
        //$suggestions = $allFound ? [] : $q->similar;
        $out = array_merge($spotsAr, $locationSuggestions);
        return response()->json($out);
    }
}