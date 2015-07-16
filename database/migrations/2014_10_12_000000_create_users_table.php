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
            $table->boolean('sex')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->point('location')->nullable();
            $table->string('time_zone', 128)->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('mail_events')->unsigned()->default(1);
            $table->tinyInteger('mail_favorites')->unsigned()->default(1);
            $table->tinyInteger('mail_followers')->unsigned()->default(1);
            $table->tinyInteger('mail_followings')->unsigned()->default(1);
            $table->tinyInteger('mail_wall')->unsigned()->default(1);
            $table->tinyInteger('mail_info')->unsigned()->default(1);
            $table->tinyInteger('mail_photo_map')->unsigned()->default(1);
            $table->boolean('notification_letter')->default(true);
            $table->boolean('notification_wall_post')->default(true);
            $table->boolean('notification_follow')->default(true);
            $table->boolean('notification_new_spot')->default(true);
            $table->boolean('notification_coming_spot')->default(true);
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
