<?php

namespace App\Http\Controllers\Xapi;

use Log;
use GuzzleHttp\Client as HttpClient;
use App\Http\Requests\Xapi\Places\AutocompleteRequest;
use App\Http\Requests\Xapi\Places\PlaceRequest;
use App\Http\Controllers\Controller;

/**
 * Class GooglePlacesAutocompleteController
 *
 * @package App\Http\Controllers\Xapi
 */
class GooglePlacesController extends Controller
{
    /**
     * Search places
     * @param AutocompleteRequest $request
     */
    public function autocomplete(AutocompleteRequest $request)
    {
        $params = $request->all();
        if($params['q'])
        {
            $params['input'] = $params['q'];
            unset($params['q']);
        }
        $response = $this->autocompleteRequest('search', $params);
        if ( isset($response['error']) ) {
            return abort($response['status']);
        }

        return response()->json($response);
    }
    
    /**
     * Search geocode
     * @param AutocompleteRequest $request
     */
    public function geocode(AutocompleteRequest $request)
    {
        $params = $request->all();
        if(isset($params['q']))
        {
            $params['address'] = $params['q'];
            unset($params['q']);
        }
        try {
            $url = config('services.places.baseUri') . config('services.places.geocodeUri');
            $json = (new HttpClient)->get($url, ['query' =>
                array_merge($params, [
                    'key'   => config('services.places.geocode_key'),
                ])
            ])->getBody();
            $data = $this->parsePlaceHttpJson($json);
        } catch (\Exception $ex) {
            Log::error('Google places autocomplete Error: ' . $ex->getMessage());
            $data = $this->parseHttpError($ex);
        }
        return response()->json($data);
    }

    /**
     * AutocompleteRequest request
     *
     * @param string $action
     * @param array $params
     * @return array|mixed
     */
    final protected function autocompleteRequest($action, array $params = [])
    {
        try {
            $req = [
                'query' =>
                array_merge([
                    'key'   => config('services.places.api_key'),
                    'types' => 'geocode'
                ], $params)
            ];
            $json = (new HttpClient)->get(config('services.places.baseUri'), $req)->getBody();

            return $this->parseHttpJson($json);
        } catch (\Exception $ex) {
            Log::error('Google places autocomplete Error: ' . $ex->getMessage());
            return $this->parseHttpError($ex);
        }
    }

    /**
     * Getting google place info by place ID
     *
     * @param AutocompleteRequest $request
     */
    final protected function place(PlaceRequest $request)
    {
        $params = $request->only(['placeid']);
        try {
            $json = (new HttpClient)->get(config('services.places.placeUri'), ['query' =>
                array_merge($params, [
                    'key'   => config('services.places.api_key'),
                ])
            ])->getBody();

            $data = $this->parsePlaceHttpJson($json);
        } catch (\Exception $ex) {
            Log::error('Google places autocomplete Error: ' . $ex->getMessage());
            $data = $this->parseHttpError($ex);
        }
        return response()->json($data);
    }

    /**
     * Parse Guzzle's response JSON
     * @param string $json
     * @return array
     * @throws \Exception
     */
    private function parseHttpJson($json)
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception();
        }

        $suggestions = [];

        if ( $data && array_key_exists("predictions", $data) && is_array($data['predictions']) ) {
            foreach($data['predictions'] as $p) {
                $suggestions[] = $p;
            }
        } else {
            Log::debug('Invalid response from google places api:');
            Log::debug(compact('data'));
        }

        return $suggestions;
    }
    
    /**
     * Parse Guzzle's response JSON for place
     * @param string $json
     * @return array
     * @throws \Exception
     */
    private function parsePlaceHttpJson($json)
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
    private function parseHttpError(\Exception $ex)
    {
        $error = ['error' => true, 'status' => 500];
        $code = $ex->getCode();
        if ($code > 200) {
            $error['status'] = $code;
        }

        return $error;
    }
}

