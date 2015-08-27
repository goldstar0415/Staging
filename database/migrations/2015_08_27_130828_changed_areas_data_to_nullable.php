<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangedAreasDataToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE areas ALTER COLUMN "data" DROP NOT NULL;');
        DB::statement('ALTER TABLE areas ALTER COLUMN "waypoints" DROP NOT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE areas ALTER COLUMN "data" NOT NULL;');
        DB::statement('ALTER TABLE areas ALTER COLUMN "waypoints" NOT NULL;');
    }
}
