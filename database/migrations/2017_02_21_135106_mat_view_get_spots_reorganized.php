<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MatViewGetSpotsReorganized extends Migration
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

        spot_points.location,
        spot_points.id as spot_point_id,
        row_number() OVER () AS primary_key
    from spots 
        inner join spot_points 
            on (spots.id = spot_points.spot_id)
        left join spot_type_categories 
            on (spots.spot_type_category_id = spot_type_categories.id)
        left join spot_types
            on (spot_type_categories.spot_type_id = spot_types.id)
);


/***************** Spots materialized table *********/
CREATE TABLE spots_mat_view AS
SELECT * from spots_view;

CREATE INDEX mvi_spot_points_locations ON spots_mat_view USING gist (location);
CREATE INDEX mvi_spots_category ON spots_mat_view USING btree (spot_type_category_id);
create index mvi_spots_created_at on spots_mat_view using btree (created_at desc nulls LAST);
create INDEX mvi_spots_start_date on spots_mat_view (start_date);
CREATE INDEX mvi_title_address ON spots_mat_view USING gin (title_address gin_trgm_ops);
CREATE UNIQUE INDEX mvi_primary ON spots_mat_view USING btree (id);


/***************** Spots replace function ***********/
CREATE OR REPLACE FUNCTION
    refresh_spots_mat_view (id INTEGER) RETURNS
    VOID SECURITY DEFINER
    AS 
    $$
        DELETE FROM spots_mat_view
            WHERE id=$1;
        INSERT INTO spots_mat_view SELECT * FROM
        spots_view WHERE id=$1;
    $$ 
    LANGUAGE SQL;
    

/***************** Spots triggers *******************/
CREATE OR REPLACE FUNCTION mv_spot_insert ()
    RETURNS TRIGGER
    AS 
    $$
        begin
            PERFORM refresh_spots_mat_view(new.id);
            RETURN new;
        end;
    $$ 
    LANGUAGE PLPGSQL;

CREATE TRIGGER mv_spot_spots_insert AFTER
    INSERT ON spots FOR EACH ROW EXECUTE
    PROCEDURE mv_spot_insert();


CREATE OR REPLACE FUNCTION mv_spot_update()
    RETURNS TRIGGER
    AS 
    $$
        begin
            if old.id=new.id then
                PERFORM refresh_spots_mat_view(new.id);
            else
                PERFORM refresh_spots_mat_view(old.id);
                PERFORM refresh_spots_mat_view(new.id);
            end if;
            RETURN new;
        end;
    $$
    LANGUAGE PLPGSQL;

CREATE TRIGGER mv_spot_spots_update AFTER
    UPDATE ON spots FOR EACH ROW EXECUTE
    PROCEDURE mv_spot_update();


CREATE OR REPLACE FUNCTION mv_spot_delete ()
    RETURNS TRIGGER
    AS 
    $$
        begin
            PERFORM refresh_spots_mat_view(old.id);
            RETURN old;
        end;
    $$ 
    LANGUAGE PLPGSQL;

CREATE TRIGGER mv_spot_spots_delete AFTER
    UPDATE ON spots FOR EACH ROW EXECUTE
    PROCEDURE mv_spot_delete();


/***************** Spot_points triggers *************/
CREATE OR REPLACE FUNCTION mv_spot_p_insert ()
    RETURNS TRIGGER
    AS 
    $$
        begin
            PERFORM refresh_spots_mat_view(new.spot_id);
            RETURN new;
        end;
    $$ 
    LANGUAGE PLPGSQL;

CREATE TRIGGER mv_spot_points_insert AFTER
    INSERT ON spot_points FOR EACH ROW EXECUTE
    PROCEDURE mv_spot_p_insert();


CREATE OR REPLACE FUNCTION mv_spot_p_update()
    RETURNS TRIGGER
    AS 
    $$
        begin
            if old.id=new.id then
                PERFORM refresh_spots_mat_view(new.spot_id);
            else
                PERFORM refresh_spots_mat_view(old.spot_id);
                PERFORM refresh_spots_mat_view(new.spot_id);
            end if;
            RETURN new;
        end;
    $$
    LANGUAGE PLPGSQL;

CREATE TRIGGER mv_spot_points_update AFTER
    UPDATE ON spot_points FOR EACH ROW EXECUTE
    PROCEDURE mv_spot_p_update();


CREATE OR REPLACE FUNCTION mv_spot_p_delete ()
    RETURNS TRIGGER
    AS 
    $$
        begin
            PERFORM refresh_spots_mat_view(old.spot_id);
            RETURN old;
        end;
    $$ 
    LANGUAGE PLPGSQL;

CREATE TRIGGER mv_spot_points_delete AFTER
    UPDATE ON spot_points FOR EACH ROW EXECUTE
    PROCEDURE mv_spot_p_delete();

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
                DROP TRIGGER IF EXISTS mv_spot_spots_insert ON spots;
                DROP TRIGGER IF EXISTS mv_spot_spots_update ON spots;
                DROP TRIGGER IF EXISTS mv_spot_spots_delete ON spots;
                DROP TRIGGER IF EXISTS mv_spot_points_insert ON spot_points;
                DROP TRIGGER IF EXISTS mv_spot_points_update ON spot_points;
                DROP TRIGGER IF EXISTS mv_spot_points_delete ON spot_points;
                DROP FUNCTION IF EXISTS refresh_spots_mat_view(id INTEGER);
                DROP FUNCTION IF EXISTS mv_spot_insert();
                DROP FUNCTION IF EXISTS mv_spot_update();
                DROP FUNCTION IF EXISTS mv_spot_delete();
                DROP FUNCTION IF EXISTS mv_spot_p_insert();
                DROP FUNCTION IF EXISTS mv_spot_p_update();
                DROP FUNCTION IF EXISTS mv_spot_p_delete();
                ");
        });

    }
}
