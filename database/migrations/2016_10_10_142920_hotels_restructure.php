<?php

use App\SpotType;
use App\SpotTypeCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\DriverManager;

class HotelsRestructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::drop('hotel_amenities');
        Schema::drop('hotels');

        Schema::create('spot_hotels', function(Blueprint $table) {
            
            $table->increments('id');
            $table->string('spot_id', 255);
            $table->string('class', 50)->nullable();
            $table->string('hotelscom_url', 256)->nullable();
            $table->string('booking_url', 256)->nullable();
            $table->integer('booking_id')->nullable();
            $table->string('booking_num_reviews')->nullable();
            $table->string('booking_rating')->nullable();
            $table->string('booking_rating_10')->nullable();
            $table->string('hotelscom_num_reviews')->nullable();
            $table->string('hotelscom_rating')->nullable();
            $table->string('facebook_url', 256)->nullable();
            $table->string('twitter_url', 256)->nullable();
            $table->string('trip_advisor_url', 256)->nullable();
            $table->string('google_pid', 256)->nullable();
            $table->string('google_rating', 20)->nullable();
            $table->string('maxrate', 20)->nullable();
            $table->string('minrate', 20)->nullable();
            $table->string('nr_rooms', 20)->nullable();
            $table->string('continent_id', 20)->nullable();
            $table->string('country_code', 20)->nullable();
            $table->string('city_hotel', 100)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('currencycode', 20)->nullable();
            $table->timestamps();
            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');
        });
        
        Schema::create('spot_amenities', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_id')->unsigned();
            $table->string('title', 255)->nullable();
            $table->string('item', 2000)->nullable();
            $table->timestamps();
            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('spot_hotels');
        Schema::drop('spot_amenities');
        
        Schema::create('hotels', function(Blueprint $table) {
            
            $table->increments('id');
            $table->string('hotel_name', 255);
            $table->string('desc_en', 5000)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('hotelscom_url', 256)->nullable();
            $table->string('booking_url', 256)->nullable();
            $table->string('homepage_url', 256)->nullable();
            $table->integer('booking_id')->nullable();
            $table->string('booking_num_reviews')->nullable();
            $table->string('booking_rating')->nullable();
            $table->string('booking_rating_10')->nullable();
            $table->string('hotelscom_num_reviews')->nullable();
            $table->string('hotelscom_rating')->nullable();
            $table->string('facebook_url', 256)->nullable();
            $table->string('twitter_url', 256)->nullable();
            $table->string('trip_advisor_url', 256)->nullable();
            $table->string('google_pid', 256)->nullable();
            $table->string('google_rating', 20)->nullable();
            $table->string('maxrate', 20)->nullable();
            $table->string('minrate', 20)->nullable();
            $table->string('nr_rooms', 20)->nullable();
            $table->point('location')->nullable();
            $table->string('continent_id', 20)->nullable();
            $table->string('country_code', 20)->nullable();
            $table->string('city_hotel', 100)->nullable();
            $table->string('address', 256)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('currencycode', 20)->nullable();
            $table->timestamps();
        });
        
        Schema::create('hotel_amenities', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_id')->unsigned();
            $table->string('title', 255)->nullable();
            $table->string('item', 2000)->nullable();
            $table->timestamps();
            $table->foreign('hotel_id')->references('id')->on('hotels')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
