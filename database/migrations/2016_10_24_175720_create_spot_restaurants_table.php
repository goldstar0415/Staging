<?php

use App\SpotTypeCategory;
//use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\DriverManager;

class CreateSpotRestaurantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!SpotTypeCategory::whereName('restaurants')->exists()) {
            SpotTypeCategory::create([
                'spot_type_id' => 4,
                'name' => 'restaurants',
                'display_name' => 'Restaurants'
            ]);
        }
        
        Schema::create('spot_restaurants', function(Blueprint $table) {
            
            $table->increments('id');
            $table->integer('spot_id');
            $table->integer('remote_id')->nullable();
            $table->string('email', 256)->nullable();
            $table->string('phone_number', 256)->nullable();
            $table->string('price_level', 50)->nullable();
            $table->string('num_trip_reviews', 50)->nullable();
            $table->string('category', 256)->nullable();
            $table->string('meals_served', 256)->nullable();
            $table->string('city', 256)->nullable();
            $table->string('state', 256)->nullable();
            $table->string('country', 256)->nullable();
            $table->string('yelp_url', 256)->nullable();
            $table->string('yelp_rating', 50)->nullable();
            $table->string('tripadvisor_id', 50)->nullable();
            $table->string('tripadvisor_url', 256)->nullable();
            $table->string('tripadvisor_rating', 50)->nullable();
            $table->string('zomato_id', 50)->nullable();
            $table->string('zomato_url', 256)->nullable();
            $table->string('zomato_rating', 50)->nullable();
            $table->string('facebook_url', 256)->nullable();
            $table->string('facebook_rating', 50)->nullable();
            $table->string('open_table_url', 256)->nullable();
            $table->string('google_pid', 50)->nullable();
            $table->string('google_rating', 50)->nullable();
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
        if ( $restSpotCategory = SpotTypeCategory::whereName('restaurants')->first() ) 
        {
            $restSpotCategory->delete();
        }
        
        Schema::drop('spot_restaurants');
    }
}
