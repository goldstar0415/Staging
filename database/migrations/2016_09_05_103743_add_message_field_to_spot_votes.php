<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageFieldToSpotVotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_votes', function (Blueprint $table) {
            $table->text('message')->nullable()->after('vote');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_votes', function (Blueprint $table) {
            $table->dropColumn(['message']);
        });
    }
}
