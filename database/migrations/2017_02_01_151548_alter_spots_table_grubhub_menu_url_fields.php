<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpotsTableGrubhubMenuUrlFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE spots ADD COLUMN grubhub_url character varying(255)');
        DB::statement('ALTER TABLE spots ADD COLUMN menu_url character varying(255)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE spots DROP COLUMNN grubhub_url');
        DB::statement('ALTER TABLE spots DROP COLUMN menu_url');
    }
}
