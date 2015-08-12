<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIconFieldsToSpotTypeCategoriesTable extends Migration {

    /**
     * Make changes to the table.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('spot_type_categories', function(Blueprint $table) {     
            
            $table->string('icon_file_name')->nullable();
            $table->integer('icon_file_size')->nullable();
            $table->string('icon_content_type')->nullable();
            $table->timestamp('icon_updated_at')->nullable();

        });

    }

    /**
     * Revert the changes to the table.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spot_type_categories', function(Blueprint $table) {

            $table->dropColumn('icon_file_name');
            $table->dropColumn('icon_file_size');
            $table->dropColumn('icon_content_type');
            $table->dropColumn('icon_updated_at');

        });
    }

}
