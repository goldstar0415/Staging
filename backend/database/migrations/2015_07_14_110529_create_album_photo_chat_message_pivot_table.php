<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumPhotoChatMessagePivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_photo_chat_message', function(Blueprint $table) {
            $table->integer('album_photo_id')->unsigned();
            $table->integer('chat_message_id')->unsigned();

            $table->foreign('album_photo_id')->references('id')->on('album_photos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('chat_message_id')->references('id')->on('chat_messages')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['album_photo_id', 'chat_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('album_photo_chat_message');
    }
}
