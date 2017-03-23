<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAreasTableAddHashField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('areas', function(Blueprint $table) {
            DB::connection()->getPdo()->exec("ALTER TABLE areas ADD hash varchar(30);");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getPdo()->exec("ALTER TABLE areas DROP hash;");
    }
}
