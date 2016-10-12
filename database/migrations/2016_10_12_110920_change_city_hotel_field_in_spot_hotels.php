<?php

use App\SpotType;
use App\SpotTypeCategory;
use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Doctrine\DBAL\DriverManager;

class ChangeCityHotelFieldInSpotHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_hotels', function(Blueprint $table) {
            $table->string('city_hotel', 256)->change();
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
            $table->string('city_hotel', 100)->change();
        });
    }
}
