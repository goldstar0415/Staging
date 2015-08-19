<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFeedsTableToMorphRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropColumn('action_type');
            $table->integer('sender_id')->unsigned()->after('user_id');
            $table->string('event_type')->after('sender_id');
            $table->morphs('feedable');

            $table->foreign('sender_id')->references('id')->on('users')
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
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropColumn(['feedable_id', 'feedable_type']);
            $table->renameColumn('event_type', 'action_type');
        });
    }
}
