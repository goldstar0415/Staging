<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypesToJsonb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE spots ALTER COLUMN "web_sites" TYPE jsonb USING web_sites::jsonb;');
        DB::statement('ALTER TABLE spots ALTER COLUMN "videos" TYPE jsonb USING videos::jsonb;');

        DB::statement('ALTER TABLE areas ALTER COLUMN "data" TYPE jsonb USING data::jsonb;');
        DB::statement('ALTER TABLE areas ALTER COLUMN "waypoints" TYPE jsonb USING waypoints::jsonb;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE spots ALTER COLUMN "web_sites" TYPE json USING web_sites::json;');
        DB::statement('ALTER TABLE spots ALTER COLUMN "videos" TYPE json USING videos::json;');

        DB::statement('ALTER TABLE areas ALTER COLUMN "data" TYPE json USING data::json;');
        DB::statement('ALTER TABLE areas ALTER COLUMN "waypoints" TYPE json USING waypoints::json;');
    }
}
