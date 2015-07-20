<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPhotoFieldsToAlbumPhotosTable extends Migration {

    /**
     * Make changes to the table.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('album_photos', function(Blueprint $table) {     
            
            $table->string('photo_file_name')->nullable();
            $table->integer('photo_file_size')->nullable();
            $table->string('photo_content_type')->nullable();
            $table->timestamp('photo_updated_at')->nullable();

        });

    }

    /**
     * Revert the changes to the table.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('album_photos', function(Blueprint $table) {

            $table->dropColumn('photo_file_name');
            $table->dropColumn('photo_file_size');
            $table->dropColumn('photo_content_type');
            $table->dropColumn('photo_updated_at');

        });
    }

}
