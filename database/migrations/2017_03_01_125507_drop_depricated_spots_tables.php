<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropDepricatedSpotsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spots', function(Blueprint $table){
                DB::connection()->getPdo()->exec("DROP TABLE IF EXISTS spot_hotels, spot_restaurants, spot_todoes;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
