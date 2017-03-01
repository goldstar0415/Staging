<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Requests\Weather\DarkskyWeatherRequest;
use GuzzleHttp\Client as HttpClient;

class WeatherController extends Controller
{
    protected $config;
    protected $http;

    public function __construct()
    {
        $this->config = config('weather');
        if (!isset($this->config['darksky'])) {
            throw new \RuntimeException('Invalid weather/darksky config');
        }

        $this->http = new HttpClient;
    }

    /**
     * Get weather from the Darksky service
     *
     * @param DarkskyWeatherRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Example: /weather/darksky?extend=hourly&lang=en&units=si&lat=10&lng=20
     *
     */
    public function darksky(DarkskyWeatherRequest $request)
    {
        $params = $request->only(['extend', 'lang', 'units']);
        $latLng = $request->only(['lat', 'lng']);

        $weatherResponse = $this->darkskyRequest($latLng, $params);

        if ( isset($weatherResponse['error']) ) {
	        return abort($weatherResponse['status']);
        }

	    return response()->json($weatherResponse);
    }

	/**
	 * Darksky request
	 *
	 * @param array $latLng
	 * @param array $params
	 * @return array|mixed
	 */
    protected function darkskyRequest(array $latLng, array $params = [])
    {
        $config = $this->config['darksky'];

        $url = sprintf('%s/forecast/%s/%s,%s', $config['baseUri'], $config['key'], $latLng['lat'], $latLng['lng']);

        $error = ['error' => true, 'status' => 500];

        try {
        	$json = $this->http->get($url, ['query' => $params])->getBody();

	        $data = json_decode($json, true);

	        if (json_last_error() !== JSON_ERROR_NONE) {
	        	throw new \Exception();
	        }

	        return $data;

        } catch (\Exception $ex) {

	        Log::error('Darksky Error: ' . $ex->getMessage());

	        $code = $ex->getCode();

	        if ($code > 200) {
		        $error['status'] = $code;
	        }

	        return $error;
        }

    }


}
