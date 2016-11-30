<?php

use App\SpotType;
use App\SpotTypeCategory;
use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\DriverManager;

class ChangeIsBookingParsedFieldInSpotHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_hotels', function(Blueprint $table) {
            $table->renameColumn('is_booking_parsed', 'is_parsed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_hotels', function(Blueprint $table) {
            $table->renameColumn('is_parsed', 'is_booking_parsed');
        });
    }
}
