<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesSocialUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_user', function (Blueprint $table) {
            $table->dropUnique(['social_id', 'user_id']);
            $table->renameColumn('token', 'social_key');
            $table->unique(['social_id', 'user_id', 'social_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('social_user', function (Blueprint $table) {
            $table->dropUnique(['social_id', 'user_id', 'social_key']);
            $table->renameColumn('social_key', 'token');
            $table->unique(['social_id', 'user_id']);
        });
    }
}
