<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsParsedFieldToSpotRestaurants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_restaurants', function (Blueprint $table) {
            $table->boolean('is_parsed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_restaurants', function (Blueprint $table) {
            $table->dropColumn('is_parsed');
        });
    }
}
