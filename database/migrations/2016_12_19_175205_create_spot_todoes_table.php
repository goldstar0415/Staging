<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotTodoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_todoes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_id')->unsigned();
            $table->string('phone_number', 50);
            $table->string('email', 50);
            $table->string('tripadvisor_url', 255);
            $table->string('tripadvisor_rating', 50);
            $table->string('tripadvisor_reviews_count', 50);
            $table->string('facebook_url', 255);
            $table->string('yelp_id', 255);
            $table->string('google_pid', 50);
            $table->string('city', 255);
            $table->string('country', 255);
            $table->string('remote_id', 50);
            $table->nullableTimestamps();
            
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
        Schema::drop('spot_todoes');
    }
}
