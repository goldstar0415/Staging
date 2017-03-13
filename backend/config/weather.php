<?php

return [
    'darksky' => [
        'key' => env('DARK_SKY_API_KEY'),
        'baseUri' => 'https://api.darksky.net',
    ],
    'openWeatherMap' => [
        'key' => env('OPENWEATHERMAP_API_KEY'),
        'baseUri' => 'http://api.openweathermap.org',
    ],
];
