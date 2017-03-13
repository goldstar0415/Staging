<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpotReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spot_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spot_id')->unsigned();
            $table->enum('reason', ['wrong', 'inappropriate', 'duplicate', 'spam', 'other'])->unsigned();
            $table->string('text', 512)->nullable();
            $table->timestamps();

            $table->foreign('spot_id')->references('id')->on('spots')
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
        Schema::drop('spot_reports');
    }
}
