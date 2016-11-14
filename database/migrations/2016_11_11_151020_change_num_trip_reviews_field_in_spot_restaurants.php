<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNumTripReviewsFieldInSpotRestaurants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE spot_restaurants RENAME num_trip_reviews TO tripadvisor_reviews_count");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE spot_restaurants RENAME tripadvisor_reviews_count TO num_trip_reviews");
    }
}
