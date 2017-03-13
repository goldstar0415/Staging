<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAreaWallPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area_wall', function(Blueprint $table) {
            $table->integer('area_id')->unsigned();
            $table->integer('wall_id')->unsigned();

            $table->foreign('area_id')->references('id')->on('areas')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('wall_id')->references('id')->on('walls')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['area_id', 'wall_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('area_wall');
    }
}
