<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaChatMessagePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_chat_message', function(Blueprint $table) {
            $table->integer('area_id')->unsigned();
            $table->integer('chat_message_id')->unsigned();

            $table->foreign('area_id')->references('id')->on('areas')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('chat_message_id')->references('id')->on('chat_messages')
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
        Schema::drop('area_chat_message');
    }
}
