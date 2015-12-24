<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTextToNullableInSpotOwnerRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_owner_requests', function (Blueprint $table) {
            $table->string('text', 5000)->nullable()->change();
            $table->string('url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_owner_requests', function (Blueprint $table) {
            $table->string('text', 5000)->change();
            $table->string('url')->change();
        });
    }
}
