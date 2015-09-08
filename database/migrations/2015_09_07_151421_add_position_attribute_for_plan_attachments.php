<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionAttributeForPlanAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->integer('position')->unsigned()->after('activity_category_id');
        });

        Schema::table('plan_spot', function (Blueprint $table) {
            $table->integer('position')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('position');
        });

        Schema::table('plan_spot', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
}
