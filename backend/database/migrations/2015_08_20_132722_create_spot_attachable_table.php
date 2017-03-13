<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotAttachableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('chat_message_spot');
        Schema::drop('spot_wall');

        Schema::create('spot_attachable', function(Blueprint $table) {
            $table->integer('spot_id')->unsigned();
            $table->morphs('spotable');
            $table->timestamps();

            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index(['spot_id', 'spotable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('spot_attachable');

        Schema::create('spot_wall', function(Blueprint $table) {
            $table->integer('spot_id')->unsigned();
            $table->integer('wall_id')->unsigned();

            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('wall_id')->references('id')->on('walls')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['spot_id', 'wall_id']);
        });

        Schema::create('chat_message_spot', function(Blueprint $table) {
            $table->integer('chat_message_id')->unsigned();
            $table->integer('spot_id')->unsigned();

            $table->foreign('chat_message_id')->references('id')->on('chat_messages')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
