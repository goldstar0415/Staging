<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumPhotoWallPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_photo_wall', function(Blueprint $table) {
            $table->integer('album_photo_id')->unsigned();
            $table->integer('wall_id')->unsigned();

            $table->foreign('album_photo_id')->references('id')->on('album_photos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('wall_id')->references('id')->on('walls')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['album_photo_id', 'wall_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('album_photo_wall');
    }
}
