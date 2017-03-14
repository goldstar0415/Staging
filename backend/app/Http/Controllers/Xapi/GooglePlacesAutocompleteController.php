<?php

namespace App\Http\Controllers;

use Log;
use GuzzleHttp\Client as HttpClient;
use App\Http\Requests\Xapi\GooglePlacesAutocomplete\AutocompleteRequest;
use App\Http\Controllers\Controller;

/**
 * Class GooglePlacesAutocompleteController
 *
 * @package App\Http\Controllers\Xapi
 */
class GooglePlacesAutocompleteController extends Controller
{
    protected $http;

    public function __construct()
    {
        $this->config = config('geocoding');
        $this->http = new HttpClient;
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
     * Mapquest request
     *
     * @param string $action
     * @param array $params
     * @return array|mixed
     */
    final protected function mapquestRequest($action, array $params = [])
    {
        $config = $this->config['mapquest'];
        $url = sprintf('%s/nominatim/v1/%s.php', $config['baseUri'], $action);

        $params['format'] = 'json';
        $params['key'] = $config['key'];

        try {

            $json = $this->http->get($url, ['query' => $params])->getBody();
            return self::parseHttpJson($json);

        } catch (\Exception $ex) {

            Log::error('Darksky Error: ' . $ex->getMessage());
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
