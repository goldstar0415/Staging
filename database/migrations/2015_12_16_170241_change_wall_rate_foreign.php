<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeWallRateForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wall_rates', function (Blueprint $table) {
            $table->dropForeign('wall_rates_user_id_foreign');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wall_rates', function (Blueprint $table) {
            $table->dropForeign('wall_rates_user_id_foreign');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade');
        });
    }
}
