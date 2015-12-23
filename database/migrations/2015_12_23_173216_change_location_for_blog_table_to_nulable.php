<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLocationForBlogTableToNulable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE blogs ALTER COLUMN "address" DROP NOT NULL;');
        DB::statement('ALTER TABLE blogs ALTER COLUMN "location" DROP NOT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE blogs ALTER COLUMN "address" SET NOT NULL;');
        DB::statement('ALTER TABLE blogs ALTER COLUMN "location" SET NOT NULL;');
    }
}
