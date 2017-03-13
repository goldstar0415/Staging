<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Forecast.io Api key
    |--------------------------------------------------------------------------
    */
    'API_KEY' => env('FORECAST_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Adding additional options on the request to Forecast.io
    |--------------------------------------------------------------------------
    | For more details and all available options check the official documentation.
    | https://developer.forecast.io/docs/v2
    */
    'options' => [
        'exclude' => 'hourly'
    ],
];
