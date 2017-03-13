<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexesSpotsSpotTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		$sql = "
			CREATE INDEX spot_points_locations ON spot_points USING gist (location);
			CREATE EXTENSION IF NOT EXISTS pg_trgm;
			CREATE INDEX spot_points_address ON spot_points USING gin (address gin_trgm_ops);
			CREATE INDEX spots_title ON spots USING gin (title gin_trgm_ops);
			CREATE INDEX spots_category ON spots USING btree (spot_type_category_id);
			CREATE INDEX spot_points_spot_id ON spot_points USING btree (spot_id);
		";
		Schema::table('spots', function(Blueprint $table) use ($sql) {
			DB::connection()->getPdo()->exec($sql);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		$sql = "
			drop index if exists spot_points_locations;
			drop index if exists spot_points_spot_id;
			drop index if exists spot_points_address;
			drop index if exists spots_title;
			drop index if exists spots_category;
			drop index if exists spot_points_spot_id;
		";
		Schema::table('spots', function(Blueprint $table) use ($sql) {
			DB::connection()->getPdo()->exec($sql);
		});
    }
}
