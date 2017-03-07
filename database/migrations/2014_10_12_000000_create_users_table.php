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
            $table->string('alias', 64)->nullable();
            $table->string('first_name', 64);
            $table->string('last_name', 64)->nullable();
            $table->string('email', 128)->unique();
            $table->string('password', 64)->nullable();
            $table->enum('sex', ['m', '', 'f'])->default('');
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->point('location')->nullable();
            $table->string('time_zone', 128)->nullable();
            $table->string('description')->nullable();
            $table->string('avatar_file_name')->nullable();
            $table->integer('avatar_file_size')->nullable();
            $table->string('avatar_content_type')->nullable();
            $table->timestamp('avatar_updated_at')->nullable();
            $table->tinyInteger('privacy_events')->unsigned()->default(1);
            $table->tinyInteger('privacy_favorites')->unsigned()->default(1);
            $table->tinyInteger('privacy_followers')->unsigned()->default(1);
            $table->tinyInteger('privacy_followings')->unsigned()->default(1);
            $table->tinyInteger('privacy_wall')->unsigned()->default(1);
            $table->tinyInteger('privacy_info')->unsigned()->default(1);
            $table->tinyInteger('privacy_photo_map')->unsigned()->default(1);
            $table->boolean('notification_letter')->default(true);
            $table->boolean('notification_wall_post')->default(true);
            $table->boolean('notification_follow')->default(true);
            $table->boolean('notification_new_spot')->default(true);
            $table->boolean('notification_coming_spot')->default(true);
            $table->string('ban_reason', 512)->nullable();
            $table->rememberToken();
            $table->string('token')->nullable();
            $table->boolean('verified')->default(false);
            $table->string('random_hash', 16)->unique();
            $table->string('vk_link', 128)->nullable();
            $table->string('facebook_link', 128)->nullable();
            $table->string('twitter_link', 128)->nullable();
            $table->string('instagram_link', 128)->nullable();
            $table->string('tumblr_link', 128)->nullable();
            $table->string('google_link', 128)->nullable();
            $table->string('custom_link', 128)->nullable();
            $table->unsignedBigInteger('ip')->nullable();
            $table->string('city', 64)->nullable();
            $table->char('country', 2)->nullable();
            $table->boolean('is_hints')->default(false);
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('last_action_at')->nullable();
            $table->timestamps();
            
            $table->unique('alias');
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
