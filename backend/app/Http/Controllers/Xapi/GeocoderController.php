<?php

namespace App\Http\Controllers\Xapi;

use Log;
use GuzzleHttp\Client as HttpClient;
use App\Http\Requests\Xapi\Geocoder\MapquestSearchRequest;
use App\Http\Requests\Xapi\Geocoder\MapquestReverseRequest;
use App\Http\Controllers\Controller;

/**
 * Class GeocoderController
 *
 * @package App\Http\Controllers
 */
class GeocoderController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Search places
     * @param MapquestSearchRequest $request
     */
    public function search(MapquestSearchRequest $request)
    {
        $params = $request->only(['addressdetails', 'limit', 'q']);

        $response = $this->mapquestRequest('search', $params);

        if ( isset($response['error']) ) {
            return abort($response['status']);
        }

        return response()->json($response);
    }

    /**
     * Reverse-geocoder
     * @param MapquestReverseRequest $request
     * @return mixed
     */
    public function reverse(MapquestReverseRequest $request)
    {
        $params = $request->only(['lat', 'lng']);

        $response = $this->mapquestRequest('reverse', [
            'lat' => $params['lat'],
            'lon' => $params['lng'],
        ]);

        if ( isset($response['error']) ) {
            return abort($response['status']);
        }

        return response()->json($response);
    }


    /**
     * Mapquest request
     *
     * @param string $action
     * @param array $params
     * @return array|mixed
     */
    final protected function mapquestRequest($action, array $params = [])
    {
        $url = sprintf('%s/nominatim/v1/%s.php',
            config('services.mapquest.baseUri'),
            $action);

        try {
            $json = (new HttpClient)->get($url, ['query' =>
                array_merge($params, [
                    'format' => 'json',
                    'key'    => config('services.mapquest.api_key'),
                ]),
            ])->getBody();

            return self::parseHttpJson($json);
        } catch (\Exception $ex) {
            Log::error('Mapquest Error: ' . $ex->getMessage());
            return self::parseHttpError($ex);
        }
    }

    /**
     * Parse Guzzle's response JSON
     * @param string $json
     * @return array
     * @throws \Exception
     */
    private static function parseHttpJson($json)
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception();
        }

        return $data;
    }

    /**
     * Parse Guzzle's HTTP error
     * @param \Exception $ex
     * @return array
     */
    private static function parseHttpError(\Exception $ex)
    {
        $error = ['error' => true, 'status' => 500];
        $code = $ex->getCode();
        if ($code > 200) {
            $error['status'] = $code;
        }

        return $error;
    }


}
