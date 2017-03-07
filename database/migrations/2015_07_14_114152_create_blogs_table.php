<?php

use Phaza\LaravelPostgis\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blogs', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('blog_category_id')->unsigned();
            $table->string('title', 255);
            $table->text('body');
            $table->string('address')->nullable();
            $table->point('location')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('main')->default(false);
            $table->integer('count_views')->default(0);
            $table->string('cover_file_name')->nullable();
            $table->integer('cover_file_size')->nullable();
            $table->string('cover_content_type')->nullable();
            $table->timestamp('cover_updated_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('blog_category_id')->references('id')->on('blog_categories')
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
        Schema::drop('blogs');
    }
}
