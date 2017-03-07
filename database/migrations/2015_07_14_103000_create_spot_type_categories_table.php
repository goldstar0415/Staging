<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotTypeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_type_categories', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_type_id')->unsigned();
            $table->string('name', 64);
            $table->string('display_name', 128);
            $table->string('icon_file_name')->nullable();
            $table->integer('icon_file_size')->nullable();
            $table->string('icon_content_type')->nullable();
            $table->timestamp('icon_updated_at')->nullable();

            $table->foreign('spot_type_id')->references('id')->on('spot_types')
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
        Schema::drop('spot_type_categories');
    }
}
