<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletesForChatMessageUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_message_user', function (Blueprint $table) {
            $table->timestamp('sender_deleted_at')->nullable();
            $table->timestamp('receiver_deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_message_user', function (Blueprint $table) {
            $table->dropColumn(['receiver_deleted_at', 'sender_deleted_at']);
        });
    }
}
