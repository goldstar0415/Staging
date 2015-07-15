<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('email', 128)->unique();
            $table->string('password', 64);
            $table->boolean('sex');
            $table->date('birth_date');
            $table->string('address');
            $table->point('location');
            $table->string('time_zone', 128);
            $table->string('description')->nullable();
            $table->tinyInteger('mail_events')->unsigned();
            $table->tinyInteger('mail_favorites')->unsigned();
            $table->tinyInteger('mail_followers')->unsigned();
            $table->tinyInteger('mail_followings')->unsigned();
            $table->tinyInteger('mail_wall')->unsigned();
            $table->tinyInteger('mail_info')->unsigned();
            $table->tinyInteger('mail_photo_map')->unsigned();
            $table->boolean('notification_letter');
            $table->boolean('notification_wall_post');
            $table->boolean('notification_follow');
            $table->boolean('notification_new_spot');
            $table->boolean('notification_coming_spot');
            $table->timestamp('banned_at')->nullable();
            $table->string('ban_reason', 512)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
