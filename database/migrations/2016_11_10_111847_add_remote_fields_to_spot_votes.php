<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemoteFieldsToSpotVotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spot_votes', function (Blueprint $table) {
            $table->integer('remote_type')->nullable();
            $table->string('remote_user_name', 50)->nullable();
            $table->string('remote_user_avatar', 300)->nullable();
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
            $table->dropColumn('remote_type');
            $table->dropColumn('remote_user_name');
            $table->dropColumn('remote_user_avatar');
        });
    }
}
