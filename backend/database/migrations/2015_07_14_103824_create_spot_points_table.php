<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_points', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_id')->unsigned();
            $table->string('address');
            $table->point('location');

            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('spot_points');
    }
}
