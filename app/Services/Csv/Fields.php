<?php

namespace App\Services\Csv;

class Fields 
{
    public static $spot = [
        'title',
        'description',
        'web_sites',
        'start_date',
        'end_date',
        'remote_id',
        'avg_rating',
        'total_reviews',
        'email',
        'phone_number',
        'country',
        'city',
        'state',
        'zip',
        'continent',
        'order_url',
        'opentable_url',
        'deal_url',
        'twitter_url',
        'tumbler_url',
        'vk_url',
        'instagram_url',
        'facebook_url',
        'facebook_rating',
        'facebook_reviews_count',
        'tripadvisor_id',
        'tripadvisor_url',
        'tripadvisor_rating',
        'tripadvisor_reviews_count',
        'yelp_id',
        'yelp_url',
        'yelp_rating',
        'yelp_reviews_count',
        'zomato_id',
        'zomato_url',
        'zomato_rating',
        'zomato_reviews_count',
        'google_id',
        'google_url',
        'google_rating',
        'google_reviews_count',
        'booking_id',
        'booking_url',
        'booking_rating',
        'booking_reviews_count',
        'hotelscom_url',
        'hotelscom_rating',
        'hotelscom_reviews_count',
        'price_level',
        'restaurant_category',
        'meals_served',
        'maxrate',
        'minrate',
        'class',
        'nr_rooms',
        'currencycode',
        'grubhub_url',
        'menu_url',
    ];
        
    public static $mass = [
        'tags',
        'photos',
        'amenities'
    ];
    
    public static $location = [
        'address',
        'latitude',
        'longitude',
    ];
}