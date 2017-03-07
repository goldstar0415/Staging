<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaAttachableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('area_chat_message');
        Schema::drop('area_wall');

        Schema::create('area_attachable', function(Blueprint $table) {
            $table->integer('area_id')->unsigned();
            $table->morphs('areaable');
            $table->timestamps();

            $table->foreign('area_id')->references('id')->on('areas')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['area_id', 'areaable_id']);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('area_attachable');

        Schema::create('area_chat_message', function(Blueprint $table) {
            $table->integer('area_id')->unsigned();
            $table->integer('chat_message_id')->unsigned();

            $table->foreign('area_id')->references('id')->on('areas')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('chat_message_id')->references('id')->on('chat_messages')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('area_wall', function(Blueprint $table) {
            $table->integer('area_id')->unsigned();
            $table->integer('wall_id')->unsigned();

            $table->foreign('area_id')->references('id')->on('areas')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('wall_id')->references('id')->on('walls')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['area_id', 'wall_id']);
        });
    }
}
