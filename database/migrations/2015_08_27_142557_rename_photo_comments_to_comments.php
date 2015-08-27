<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePhotoCommentsToComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('photo_comments', 'comments');
        Schema::drop('plan_comments');
        Schema::drop('spot_comments');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('comments', 'photo_comments');
        Schema::create('plan_comments', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('plan_id')->unsigned();
            $table->string('body', 5000);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')
                ->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::create('spot_comments', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('body', 5000);
            $table->timestamps();

            $table->foreign('spot_id')->references('id')->on('spots')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }
}
