<?php

namespace App\Services\Csv;

class Fields 
{
    public static $shelter = [
        'hotel_name' => 'title',
        'desc_en' => 'description',
        'homepage_url' => 'web_sites',
        'avg_rating' => 'avg_rating',
        'total_reviews' => 'total_reviews',
        'class' => 'class',
        'hotelscom_url' => 'hotelscom_url',
        'booking_url' => 'booking_url',
        'booking_id' => 'booking_id',
        'booking_num_reviews' => 'booking_reviews_count',
        'booking_rating' => 'booking_rating',
        'hotelscom_num_reviews' => 'hotelscom_reviews_count',
        'hotelscom_rating' => 'hotelscom_rating',
        'facebook_url' => 'facebook_url',
        'twitter_url' => 'twitter_url',
        'trip_advisor_url' => 'tripadvisor_url',
        'google_pid' => 'google_id',
        'google_rating' => 'google_rating',
        'maxrate' => 'maxrate',
        'minrate' => 'minrate',
        'nr_rooms' => 'nr_rooms',
        'continent_id' => 'continent',
        'country_code' => 'country',
        'city_hotel' => 'city',
        'zip' => 'zip',
        'currencycode' => 'currencycode'
    ];
    
    public static $shelterMass = [
        'photo_url' => 'photos',
        'tags' => 'tags'
    ];
    
    public static $shelterLocation = [
        'address' => 'address',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
    ];
        
    public static $todo    = [
        'todo_name' => 'title',
        'description' => 'description',
        'website' => 'web_sites',
        'avg_rating' => 'avg_rating',
        'total_reviews' => 'total_reviews',
        'todo_id' => 'remote_id',
        'email' => 'email',
        'phone' => 'phone_number',
        'trip_url' => 'tripadvisor_url',
        'trip_rating' => 'tripadvisor_rating',
        'trip_no_reviews' => 'tripadvisor_reviews_count',
        'city' => 'city',
        'country' => 'country',
        'google_id' => 'google_id',
        'facebook_url' => 'facebook_url',
        'yelp_id' => 'yelp_id',
    ];
    
    public static $todoMass = [
        'images' => 'photos',
        'tags' => 'tags'
    ];
    
    public static $todoLocation = [
        'street_address' => 'address',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
    ];
    
    public static $event   = [
        'title' => 'title',
        'start_date' => 'start_date',
        'end_date' => 'end_date',
        'description' => 'description',
        'website' => 'web_sites',
        'city' => 'city',
        'state' => 'state',
        'e-mail' => 'email',
    ];
    
    public static $eventMass = [
        'picture' => 'photoss',
        'tags' => 'tags'
    ];
    
    public static $eventLocation = [
        'full_address' => 'address',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
    ];
    
    public static $food    = [
        'restaurant name' => 'title',
        'description' => 'description',
        'website' => 'web_sites',
        'avg_rating' => 'avg_rating',
        'total_reviews' => 'total_reviews',
        'rest_id' => 'remote_id',
        'trip_id' => 'tripadvisor_id',
        'tripadvisor url' => 'tripadvisor_url',
        'email' => 'email',
        'phone number' => 'phone_number',
        'trip_rating' => 'tripadvisor_rating',
        'price_level' => 'price_level',
        'num_trip_reviews' => 'tripadvisor_reviews_count',
        'category' => 'restaurant_category',
        'meals_served' => 'meals_served',
        'country' => 'country',
        'city' => 'city',
        'state' => 'state',
        'yelp' => 'yelp_url',
        'yelp_rating' => 'yelp_rating',
        'zomato' => 'zomato_url',
        'zomato_id' => 'zomato_id',
        'zomatorating' => 'zomato_rating',
        'facebook_url' => 'facebook_url',
        'facebook_rating' => 'facebook_rating',
        'opentableurl' => 'opentable_url',
        'google_pid' => 'google_id',
        'google_rating' => 'google_rating',
    ];
    
    public static $foodMass = [
        'tags' => 'tags',
        'all_images' => 'photos',
        'features' => 'amenities'
    ];
    
    public static $foodLocation = [
        'address' => 'address',
        'latitude' => 'latitude',
        'longitude' => 'longitude',
    ];
    
    public static $updateRules = [
        'title' => 'required|max:255',
        'description' => 'max:2000',
        'web_sites' => 'sometimes|array',
        'email' => 'max:50',
        'phone_number' => 'max:50',
        'country' => 'max:255',
        'city' => 'max:255',
        'state' => 'max:255',
        'zip' => 'max:20',
        'continent' => 'max:20', //continent_id
        'opentable_url' => 'max:255', //open_table_url (in restaurants)
        'twitter_url' => 'max:255',
        'tumbler_url' => 'max:255',
        'vk_url'      => 'max:255',
        'instagram_url' => 'max:255',
        'facebook_url' => 'max:255',
        'facebook_rating' => 'max:50', //new
        'facebook_reviews_count' => 'max:50', //new
        'tripadvisor_id' => 'max:50',
        'tripadvisor_url' => 'max:255', //trip_advisor_url (in hotels)
        'tripadvisor_rating' => 'max:50',
        'tripadvisor_reviews_count' => 'max:50', //num_trip_reviews
        'yelp_id' => 'max:50',
        'yelp_url' => 'max:255',
        'yelp_rating' => 'max:50',
        'yelp_reviews_count' => 'max:50', //new
        'zomato_id' => 'max:50',
        'zomato_url' => 'max:255',
        'zomato_rating' => 'max:50',
        'zomato_reviews_count' => 'max:50', //new
        'google_id' => 'max:50', // lately google_pid
        'google_url' => 'max:255',
        'google_rating' => 'max:50',
        'google_reviews_count' => 'max:50',
        'booking_url' => 'max:255',
        'booking_rating' => 'max:50',
        'booking_reviews_count' => 'max:50', //booking_num_reviews
        'hotelscom_url' => 'max:255',
        'hotelscom_rating' => 'max:50',
        'hotelscom_reviews_count' => 'max:50', //hotelscom_num_reviews
        'price_level' => 'max:50',
        'restaurant_category' => 'max:255',
        'meals_served' => 'max:255',
        'maxrate' => 'max:20',
        'minrate' => 'max:20',
        'class' => 'max:50',
        'nr_rooms' => 'max:20',
        'currencycode' => 'max:20'
    ];
}