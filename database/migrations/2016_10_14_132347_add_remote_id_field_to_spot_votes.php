<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemoteIdFieldToSpotVotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_votes', function (Blueprint $table) {
            $table->string('remote_id', 255)->nullable();
            $table->integer('user_id')->nullable()->change();
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
            $table->dropColumn('remote_id');
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
