<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSocialLinksToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('vk_link', 128)->nullable();
            $table->string('facebook_link', 128)->nullable();
            $table->string('twitter_link', 128)->nullable();
            $table->string('instagram_link', 128)->nullable();
            $table->string('tumblr_link', 128)->nullable();
            $table->string('google_link', 128)->nullable();
            $table->string('custom_link', 128)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vk_link');
            $table->dropColumn('facebook_link');
            $table->dropColumn('twitter_link');
            $table->dropColumn('instagram_link');
            $table->dropColumn('tumblr_link');
            $table->dropColumn('google_link');
            $table->dropColumn('custom_link');
        });
    }
}
