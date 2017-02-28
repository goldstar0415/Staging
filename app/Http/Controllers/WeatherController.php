<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Requests\Weather\DarkskyWeatherRequest;

class WeatherController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = config('weather');
        if (!isset($this->config['darksky'])) {
            throw new \RuntimeException('Invalid weather/darksky config');
        }
    }

    /**
     * Get weather from the Darksky service
     * @param DarkskyWeatherRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function darksky(DarkskyWeatherRequest $request)
    {
        $params = $request->only(['extend', 'lang', 'units']);
        $latLng = $request->only(['lat', 'lng']);

        $config = $this->config['darksky'];

        return response()->json(compact('params', 'latLng', 'config'));
    }

    protected function darkskyRequest(array $latLng, array $params = [])
    {
        $config = $this->config['darksky'];
        $url = sprintf('%s/forecast/%s/%s,%s', $config['baseUri'], $config['key'], $latLng['lat'], $latLng['lng']);



    }


}
