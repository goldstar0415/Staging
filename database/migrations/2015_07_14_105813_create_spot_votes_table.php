<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_votes', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->unsigned();
            $table->integer('spot_id')->unsigned();
            $table->string('remote_id', 255)->nullable();
            $table->integer('remote_type')->nullable();
            $table->string('remote_user_name', 50)->nullable();
            $table->string('remote_user_avatar', 300)->nullable();
            $table->tinyInteger('vote');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('spot_votes');
    }
}
