<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAreaableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('area_attachable', function (Blueprint $table) {
            $table->dropIndex(['areable_id', 'areable_type']);
            $table->dropPrimary(['area_id', 'areable_id']);

            $table->renameColumn('areable_id', 'areaable_id');
            $table->renameColumn('areable_type', 'areaable_type');

            $table->index(['areaable_id', 'areaable_type']);
            $table->primary(['area_id', 'areaable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('area_attachable', function (Blueprint $table) {
            $table->dropIndex(['areaable_id', 'areaable_type']);
            $table->dropPrimary(['area_id', 'areaable_id']);

            $table->renameColumn('areaable_id', 'areable_id');
            $table->renameColumn('areaable_type', 'areable_type');

            $table->index(['areable_id', 'areable_type']);
            $table->primary(['area_id', 'areable_id']);
        });
    }
}
