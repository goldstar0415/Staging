<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpotRestaurantsCreateRemoteIdIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_restaurants', function (Blueprint $table) {
            $table->index('remote_id');
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
            $table->dropIndex('remote_id');
        });
    }
}
