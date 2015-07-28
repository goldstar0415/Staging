<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friends', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->date('birth_date')->nullable();
            $table->string('phone', 24)->nullable();
            $table->string('email', 128)->nullable();
            $table->string('address')->nullable();
            $table->point('location')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('friends');
    }
}
