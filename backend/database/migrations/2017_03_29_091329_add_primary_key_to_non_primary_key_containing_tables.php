<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrimaryKeyToNonPrimaryKeyContainingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('album_photo_attachable', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('calendar_spots', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('chat_message_user', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('email_changes', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('migrations', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('password_resets', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('plan_user', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('reviews', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('social_user', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('spot_attachable', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('spot_user', function(Blueprint $table) {
            $table->increments('id');
        });
        Schema::table('spots_mat_view', function(Blueprint $table) {
            $table->increments('id_for_pglogical');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('album_photo_attachable', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('calendar_spots', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('chat_message_user', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('email_changes', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('migrations', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('password_resets', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('plan_user', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('reviews', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('social_user', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('spot_attachable', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('spot_user', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
        Schema::table('spots_mat_view', function(Blueprint $table) {
            $table->dropPrimary('id');
            $table->dropColumn('id');
        });
    }
}

