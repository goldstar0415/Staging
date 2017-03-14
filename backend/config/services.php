<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'facebook' => [
        'client_id' => env('FB_CLIENT_ID'),
        'client_secret' => env('FB_CLIENT_SECRET'),
        'redirect' => env('FB_REDIRECT_URL'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],
    'darksky' => [
        'key'     => env('DARK_SKY_API_KEY'),
        'baseUri' => 'https://api.darksky.net',
    ],
    'openweathermap' => [
        'key'     => env('OPENWEATHERMAP_API_KEY'),
        'baseUri' => 'http://api.openweathermap.org',
    ],
    'mapquest' => [
        'key'     => env('MAPQUEST_API_KEY'),
        'baseUri' => 'http://open.mapquestapi.com',
    ],
    'yelp' => [
        'client_secret' => env('YELP_CLIENT_SECRET'),
        'client_id'     => env('YELP_CLIENT_ID'),
    ],
    'ticketmaster' => [
        'apikey'   => env('TICKETMASTER_APIKEY'),
        'secret'   => env('TICKETMASTER_SECRET'),
        'user'     => env('TICKETMASTER_USER'),
        'password' => env('TICKETMASTER_PASSWORD'),
    ],
    'places' => [
        'key'     => env('GOOGLE_PLACES_API_KEY'),
        'baseUri' => 'https://maps.googleapis.com/maps/api/place/'
    ],
    'seatgeek' => [
        'client_id' => env('SEATGEEK_CLIENT_ID'),
        'secret'    => env('SEATGEEK_SECRET'),
    ],
];
