<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalReviewsAverageRatingToSpot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE spots ADD COLUMN avg_rating float NULL;');
        DB::statement('ALTER TABLE spots ADD COLUMN total_reviews integer NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE spots DROP COLUMN avg_rating;');
        DB::statement('ALTER TABLE spots DROP COLUMN total_reviews;');
    }
}
