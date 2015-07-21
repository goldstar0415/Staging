<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_photos', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_id')->unsigned();
            $table->timestamps();

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
        Schema::drop('spot_photos');
    }
}
