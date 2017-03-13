<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('title');
            $table->string('description', 5000)->nullable();
            $table->jsonb('data')->nullable();
            $table->jsonb('waypoints')->nullable();
            $table->string('cover_file_name')->nullable();
            $table->integer('cover_file_size')->nullable();
            $table->string('cover_content_type')->nullable();
            $table->timestamp('cover_updated_at')->nullable();
            $table->tinyInteger('zoom')->unsigned()->nullable();
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
        Schema::drop('areas');
    }
}
