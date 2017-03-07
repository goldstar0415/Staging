<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlbumPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_photos', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('album_id')->unsigned();
            $table->string('address')->nullable();
            $table->point('location')->nullable();
            $table->string('photo_file_name')->nullable();
            $table->integer('photo_file_size')->nullable();
            $table->string('photo_content_type')->nullable();
            $table->timestamp('photo_updated_at')->nullable();
            $table->timestamps();

            $table->foreign('album_id')->references('id')->on('albums')
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
        Schema::drop('album_photos');
    }
}
