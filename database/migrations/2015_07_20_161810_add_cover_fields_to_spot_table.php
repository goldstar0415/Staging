<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCoverFieldsToSpotTable extends Migration {

    /**
     * Make changes to the table.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('spots', function(Blueprint $table) {
            
            $table->string('cover_file_name')->nullable();
            $table->integer('cover_file_size')->nullable();
            $table->string('cover_content_type')->nullable();
            $table->timestamp('cover_updated_at')->nullable();

        });

    }

    /**
     * Revert the changes to the table.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spots', function(Blueprint $table) {

            $table->dropColumn('cover_file_name');
            $table->dropColumn('cover_file_size');
            $table->dropColumn('cover_content_type');
            $table->dropColumn('cover_updated_at');

        });
    }

}
