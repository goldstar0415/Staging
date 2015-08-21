<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSexColumnToEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("CREATE TYPE sex_type AS ENUM ('m', '', 'f');");
            DB::statement("ALTER TABLE users ALTER COLUMN sex TYPE sex_type USING ''");
            DB::statement("ALTER TABLE users ALTER COLUMN sex SET DEFAULT '';");
            DB::statement("ALTER TABLE users ALTER COLUMN sex SET NOT NULL;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users ALTER COLUMN sex DROP NOT NULL;");
            DB::statement("ALTER TABLE users ALTER COLUMN sex SET DEFAULT NULL;");
            DB::statement("ALTER TABLE users ALTER COLUMN sex TYPE bool USING NULL;");
            DB::statement("DROP TYPE sex_type;");
        });
    }
}
