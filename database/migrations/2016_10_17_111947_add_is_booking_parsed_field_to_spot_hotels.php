<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsBookingParsedFieldToSpotHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_hotels', function (Blueprint $table) {
            $table->boolean('is_booking_parsed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_hotels', function (Blueprint $table) {
            $table->dropColumn('is_booking_parsed');
        });
    }
}
