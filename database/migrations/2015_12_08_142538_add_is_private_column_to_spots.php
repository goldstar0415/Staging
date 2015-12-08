<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsPrivateColumnToSpots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE spots ALTER COLUMN is_approved SET DEFAULT FALSE, ALTER COLUMN is_approved SET NOT NULL;");
        Schema::table('spots', function (Blueprint $table) {
            $table->boolean('is_private')->default(false)->after('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE spots ALTER COLUMN is_approved DROP DEFAULT, ALTER COLUMN is_approved DROP NOT NULL;");
        Schema::table('spots', function (Blueprint $table) {
            $table->dropColumn('is_private');
        });
    }
}
