<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatViewSpotsRemoveOld extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spots', function(Blueprint $table){
                DB::connection()->getPdo()->exec("DROP MATERIALIZED VIEW IF EXISTS mv_spots_spot_points;");
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
            CREATE MATERIALIZED VIEW mv_spots_spot_points as
                    select
                            spots.id,
                            spots.spot_type_category_id,
                            concat_ws(' ', spots.title::text, spot_points.address::text) AS title_address,
                            spots.start_date,
                            spots.end_date,
                            spots.created_at,
                            spots.is_approved,
                            spots.is_private,

                            spot_points.location,
                            spot_points.id as spot_point_id,
                            row_number() OVER () AS primary_key
                    from
                            public.spots inner join public.spot_points on (spots.id = spot_points.spot_id);

            CREATE INDEX mvsp_spot_points_locations ON mv_spots_spot_points USING gist (location);
            CREATE INDEX mvsp_spots_category ON mv_spots_spot_points USING btree (spot_type_category_id);
            create index mvsp_spots_created_at on mv_spots_spot_points using btree (created_at desc nulls LAST);
            create INDEX msvp_spots_start_date on mv_spots_spot_points (start_date);
            CREATE INDEX mvsp_title_address ON mv_spots_spot_points USING gin (title_address gin_trgm_ops);
            CREATE UNIQUE INDEX mvsp_primary ON mv_spots_spot_points USING btree (primary_key);
    ";
        Schema::table('spots', function(Blueprint $table) use ($sql) {
            DB::connection()->getPdo()->exec($sql);
        });
		
    }
}
