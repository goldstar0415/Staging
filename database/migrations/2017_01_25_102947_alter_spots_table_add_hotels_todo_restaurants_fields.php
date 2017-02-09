<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpotsTableAddHotelsTodoRestaurantsFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE spots ADD COLUMN email varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN phone_number varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN country varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN city varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN state varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN zip varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN continent varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN order_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN opentable_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN deal_url varchar(255) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN twitter_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN tumbler_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN vk_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN instagram_url varchar(255) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN facebook_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN facebook_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN facebook_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN tripadvisor_id varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN tripadvisor_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN tripadvisor_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN tripadvisor_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN yelp_id varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN yelp_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN yelp_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN yelp_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN zomato_id varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN zomato_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN zomato_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN zomato_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN google_id varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN google_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN google_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN google_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN booking_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN booking_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN booking_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN hotelscom_url varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN hotelscom_rating varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN hotelscom_reviews_count varchar(50) NULL');
        
        DB::statement('ALTER TABLE spots ADD COLUMN price_level varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN category varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN meals_served varchar(255) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN maxrate varchar(20) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN minrate varchar(20) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN class varchar(50) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN nr_rooms varchar(20) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN currencycode varchar(20) NULL');
        DB::statement('ALTER TABLE spots ADD COLUMN hours jsonb NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE spots DROP COLUMN email');
        DB::statement('ALTER TABLE spots DROP COLUMN phone_number');
        
        DB::statement('ALTER TABLE spots DROP COLUMN country');
        DB::statement('ALTER TABLE spots DROP COLUMN city');
        DB::statement('ALTER TABLE spots DROP COLUMN state');
        DB::statement('ALTER TABLE spots DROP COLUMN zip');
        DB::statement('ALTER TABLE spots DROP COLUMN continent');
        
        DB::statement('ALTER TABLE spots DROP COLUMN order_url');
        DB::statement('ALTER TABLE spots DROP COLUMN opentable_url');
        DB::statement('ALTER TABLE spots DROP COLUMN deal_url');
        
        DB::statement('ALTER TABLE spots DROP COLUMN twitter_url');
        DB::statement('ALTER TABLE spots DROP COLUMN tumbler_url');
        DB::statement('ALTER TABLE spots DROP COLUMN vk_url');
        DB::statement('ALTER TABLE spots DROP COLUMN instagram_url');
        
        DB::statement('ALTER TABLE spots DROP COLUMN facebook_url');
        DB::statement('ALTER TABLE spots DROP COLUMN facebook_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN facebook_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN tripadvisor_id');
        DB::statement('ALTER TABLE spots DROP COLUMN tripadvisor_url');
        DB::statement('ALTER TABLE spots DROP COLUMN tripadvisor_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN tripadvisor_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN yelp_id');
        DB::statement('ALTER TABLE spots DROP COLUMN yelp_url');
        DB::statement('ALTER TABLE spots DROP COLUMN yelp_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN yelp_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN zomato_id');
        DB::statement('ALTER TABLE spots DROP COLUMN zomato_url');
        DB::statement('ALTER TABLE spots DROP COLUMN zomato_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN zomato_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN google_id');
        DB::statement('ALTER TABLE spots DROP COLUMN google_url');
        DB::statement('ALTER TABLE spots DROP COLUMN google_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN google_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN booking_url');
        DB::statement('ALTER TABLE spots DROP COLUMN booking_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN booking_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN hotelscom_url');
        DB::statement('ALTER TABLE spots DROP COLUMN hotelscom_rating');
        DB::statement('ALTER TABLE spots DROP COLUMN hotelscom_reviews_count');
        
        DB::statement('ALTER TABLE spots DROP COLUMN price_level');
        DB::statement('ALTER TABLE spots DROP COLUMN category');
        DB::statement('ALTER TABLE spots DROP COLUMN meals_served');
        DB::statement('ALTER TABLE spots DROP COLUMN maxrate');
        DB::statement('ALTER TABLE spots DROP COLUMN minrate');
        DB::statement('ALTER TABLE spots DROP COLUMN class');
        DB::statement('ALTER TABLE spots DROP COLUMN nr_rooms');
        DB::statement('ALTER TABLE spots DROP COLUMN currencycode');
        DB::statement('ALTER TABLE spots DROP COLUMN hours');
    }
}
