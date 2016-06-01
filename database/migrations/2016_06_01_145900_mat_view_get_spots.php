<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatViewGetSpots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		$sql = "
			CREATE MATERIALIZED VIEW mv_spots_spot_points as
				select
					public.spots.*,
					public.spot_points.location,
					public.spot_points.address,
					public.spot_points.spot_point_id
				from
					public.spots inner join public.spot_points on (spots.id = spot_points.spot_id);
			
			CREATE INDEX mvsp_spot_points_locations ON mv_spots_spot_points USING gist (location);
			CREATE INDEX mvsp_spots_category ON mv_spots_spot_points USING btree (spot_type_category_id);
			create index mvsp_spots_updated_at on mv_spots_spot_points using btree (updated_at desc nulls LAST);
			CREATE UNIQUE INDEX mvsp_spots_pkey ON mv_spots_spot_points USING btree (id);
			create INDEX msvp_spots_start_date on mv_spots_spot_points (start_date);
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
			DROP MATERIALIZED VIEW IF EXISTS mv_spots_spot_points;
		";
		Schema::table('spots', function(Blueprint $table) use ($sql) {
			DB::connection()->getPdo()->exec($sql);
		});
    }
}
