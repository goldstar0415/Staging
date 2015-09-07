<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIconFieldsToActivityCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_categories', function(Blueprint $table) {

            $table->string('icon_file_name')->nullable();
            $table->integer('icon_file_size')->nullable();
            $table->string('icon_content_type')->nullable();
            $table->timestamp('icon_updated_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_categories', function(Blueprint $table) {

            $table->dropColumn('icon_file_name');
            $table->dropColumn('icon_file_size');
            $table->dropColumn('icon_content_type');
            $table->dropColumn('icon_updated_at');

        });
    }
}
