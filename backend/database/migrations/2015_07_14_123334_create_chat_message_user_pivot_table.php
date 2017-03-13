<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatMessageUserPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_message_user', function(Blueprint $table) {
            $table->integer('chat_message_id')->unsigned();
            $table->integer('sender_id')->unsigned();
            $table->integer('receiver_id')->unsigned();
            $table->timestamp('sender_deleted_at')->nullable();
            $table->timestamp('receiver_deleted_at')->nullable();

            $table->foreign('chat_message_id')->references('id')->on('chat_messages')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->index(['sender_id', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chat_message_user');
    }
}
