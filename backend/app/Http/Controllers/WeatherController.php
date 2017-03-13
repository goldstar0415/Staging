<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Requests\Weather\DarkskyWeatherRequest;
use App\Http\Requests\Weather\OpenWeatherMapRequest;
use GuzzleHttp\Client as HttpClient;
use Cache;
use Carbon\Carbon;

/**
 * Class WeatherController
 * This is a proxy for weather requests with private API keys
 *
 * @package App\Http\Controllers
 */
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
     * Get OpenWeatherMap data
     *
     * @param OpenWeatherMapRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Example: /weather/openweathermap?bbox=37.5538444519043,55.748854450820616,37.65109062194824,55.791534783573184,14&cluster=yes&cnt=10&units=imperial
     */
    public function openWeatherMap(OpenWeatherMapRequest $request)
    {
        $params = $request->only(['bbox', 'cluster', 'units', 'cnt']);

        $weatherResponse = $this->openWeatherMapRequest($params);

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
    final protected function darkskyRequest(array $latLng, array $params = [])
    {
        $config = $this->config['darksky'];
        $url = sprintf('%s/forecast/%s/%s,%s', $config['baseUri'], $config['key'], $latLng['lat'], $latLng['lng']);
        if(Cache::has($url))
        {
            return Cache::get($url);
        }
        try {

        	$json = $this->http->get($url, ['query' => $params])->getBody();
                $result = self::parseHttpJson($json);
                Cache::put($url, $result, Carbon::now()->addDay());
	        return $result;

        } catch (\Exception $ex) {

	        Log::error('Darksky Error: ' . $ex->getMessage());
	        return self::parseHttpError($ex);
        }

    }

    /**
     * OpenWeatherMap request
     *
     * @param array $params
     * @return array|mixed
     */
    final protected function openWeatherMapRequest($params)
    {
        $config = $this->config['openWeatherMap'];
        $url = sprintf('%s/data/2.5/box/city', $config['baseUri'] );
        $params['APPID'] = $config['key'];

        try {

            $json = $this->http->get($url, ['query' => $params])->getBody();
            return self::parseHttpJson($json);

        } catch (\Exception $ex) {

            Log::error('OpenWeatherMap Error: ' . $ex->getMessage());
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
