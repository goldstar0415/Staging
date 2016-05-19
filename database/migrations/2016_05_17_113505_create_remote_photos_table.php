<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemotePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (!Schema::hasTable('remote_photos')) {
			Schema::create('remote_photos', function (Blueprint $table) {
				$table->increments('id');
				$table->morphs('associated');
				$table->integer('image_type');
				$table->string('url');
				$table->string('size')->nullable();
				$table->nullableTimestamps();
			});
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('remote_photos');
    }
}
