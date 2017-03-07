<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViewGetSpots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spots', function(Blueprint $table) {
            DB::connection()->getPdo()->exec("
/***************** Spots view ***********************/

DROP VIEW IF EXISTS spots_view;
CREATE VIEW spots_view AS
(
    select
        spots.id,
        spots.spot_type_category_id,
        concat_ws(' ', spots.title::text, spot_points.address::text) AS title_address,
        spots.start_date,
        spots.end_date,
        spots.created_at,
        spots.is_approved,
        spots.is_private,
        spots.title,
        spot_points.address,
        spots.minrate,
        spots.maxrate,
        spots.currencycode,
        spots.avg_rating,
        spots.total_reviews,
        spot_types.name as type_name,
        spot_types.display_name as type_display_name,
        spots.user_id,
        spot_points.location,
        spot_points.id as spot_point_id,
        remote_photos.url as remote_cover,
        spots.cover_file_name as cover,
        setweight(to_tsvector(coalesce(spots.title::text, '')), 'A') || setweight(to_tsvector(coalesce(spot_points.address::text, '')), 'B') as fts,

        row_number() OVER () AS primary_key
    from spots 
        inner join spot_points 
            on (spots.id = spot_points.spot_id)
        left join spot_type_categories 
            on (spots.spot_type_category_id = spot_type_categories.id)
        left join spot_types
            on (spot_type_categories.spot_type_id = spot_types.id)
        left join (
            select distinct associated_id, id ,url, image_type 
                from remote_photos 
                where associated_type = 'App\Spot' 
                    and image_type = 1) as rps 
            on (rps.associated_id = spots.id)
        left join remote_photos 
            on rps.id = remote_photos.id
);

/***************** Spots materialized table *********/
DROP TABLE IF EXISTS spots_mat_view;

CREATE TABLE spots_mat_view AS
SELECT * from spots_view;

CREATE INDEX mvi_spot_points_locations ON spots_mat_view USING gist (location);
CREATE INDEX mvi_spots_category ON spots_mat_view USING btree (spot_type_category_id);
create index mvi_spots_created_at on spots_mat_view using btree (created_at desc nulls LAST);
create INDEX mvi_spots_start_date on spots_mat_view (start_date);
CREATE INDEX mvi_title_address ON spots_mat_view USING gin (title_address gin_trgm_ops);
CREATE UNIQUE INDEX mvi_primary ON spots_mat_view USING btree (id);
                ");
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spots', function(Blueprint $table) {
            DB::connection()->getPdo()->exec("
                DROP VIEW IF EXISTS spots_view;
                DROP TABLE IF EXISTS spots_mat_view;
                ");
        });

    }
}
