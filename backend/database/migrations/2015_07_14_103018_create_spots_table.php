<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spots', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('spot_type_category_id')->unsigned();
            $table->string('remote_id')->nullable();
            $table->string('title', 255);
            $table->string('description', 5000)->nullable();
            $table->jsonb('web_sites')->nullable();
            $table->jsonb('videos')->nullable();
            $table->float('avg_rating')->nullable();
            $table->integer('total_reviews')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('cover_file_name')->nullable();
            $table->integer('cover_file_size')->nullable();
            $table->string('cover_content_type')->nullable();
            $table->timestamp('cover_updated_at')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('zip', 50)->nullable();
            $table->string('continent', 50)->nullable();
            $table->string('order_url', 255)->nullable();
            $table->string('opentable_url', 255)->nullable();
            $table->string('deal_url', 255)->nullable();
            $table->string('grubhub_url', 255)->nullable();
            $table->string('menu_url', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->string('tumbler_url', 255)->nullable();
            $table->string('vk_url', 255)->nullable();
            $table->string('instagram_url', 255)->nullable();
            $table->string('facebook_url', 255)->nullable();
            $table->string('facebook_rating', 50)->nullable();
            $table->string('facebook_reviews_count', 50)->nullable();
            $table->string('tripadvisor_id', 50)->nullable();
            $table->string('tripadvisor_url', 255)->nullable();
            $table->string('tripadvisor_rating', 50)->nullable();
            $table->string('tripadvisor_reviews_count', 50)->nullable();
            $table->string('yelp_id', 255)->nullable();
            $table->string('yelp_url', 255)->nullable();
            $table->string('yelp_rating', 50)->nullable();
            $table->string('yelp_reviews_count', 50)->nullable();
            $table->string('zomato_id', 50)->nullable();
            $table->string('zomato_url', 255)->nullable();
            $table->string('zomato_rating', 50)->nullable();
            $table->string('zomato_reviews_count', 50)->nullable();
            $table->string('google_id', 50)->nullable();
            $table->string('google_url', 255)->nullable();
            $table->string('google_rating', 50)->nullable();
            $table->string('google_reviews_count', 50)->nullable();
            $table->string('booking_url', 255)->nullable();
            $table->string('booking_rating', 50)->nullable();
            $table->string('booking_reviews_count', 50)->nullable();
            $table->string('hotelscom_url', 255)->nullable();
            $table->string('hotelscom_rating', 50)->nullable();
            $table->string('hotelscom_reviews_count', 50)->nullable();
            $table->string('price_level', 50)->nullable();
            $table->string('restaurant_category', 255)->nullable();
            $table->string('meals_served', 255)->nullable();
            $table->string('maxrate', 20)->nullable();
            $table->string('minrate', 20)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('nr_rooms', 20)->nullable();
            $table->string('currencycode', 20)->nullable();
            $table->jsonb('hours')->nullable();
            
            
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_private')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->foreign('spot_type_category_id')->references('id')->on('spot_type_categories');
            $table->index('remote_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('spots');
    }
}
