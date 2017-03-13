<?php

namespace App\Services;

use Storage;

class GoogleAddress
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * Google api keys storage
     *
     * @var array
     */
    private $api_keys = [];

    /**
     * Current api key
     *
     * @var string
     */
    private $current_key = '';

    /**
     * GoogleAddress constructor.
     */
    public function __construct()
    {
        $this->http = new \GuzzleHttp\Client;
        $this->getApiKeys();
        $this->current_key = array_pop($this->api_keys);
    }

    public function get($lat, $lng)
    {
        $response = null;
        $data = null;

        do {
            $response = $this->http->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'latlng' => $lat . ',' . $lng,
                    'key' => $this->current_key
                ]
            ]);

            $data = json_decode((string)$response->getBody(), true);
        } while (
            isset($data['error_message']) and
            !empty($this->api_keys) and
            $this->current_key = array_pop($this->api_keys)
        );

        return ($data and !isset($data['error_message'])) ? $data['results'][0]['formatted_address'] : '';
    }

    /**
     * @return array
     */
    public function getApiKeys()
    {
        if (empty($this->api_keys)) {
            $this->api_keys = json_decode(Storage::get('googleapi-keys.json'))->keys;
        }

        return $this->api_keys;
    }
}
