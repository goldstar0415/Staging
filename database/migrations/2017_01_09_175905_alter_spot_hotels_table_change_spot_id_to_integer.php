<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSpotHotelsTableChangeSpotIdToInteger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE spot_hotels ALTER COLUMN spot_id TYPE integer USING spot_id::integer');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE spot_hotels ALTER COLUMN spot_id TYPE character varyin USING spot_id::integer');
    }
}
