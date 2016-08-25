<?php

use App\SpotType;
use App\SpotTypeCategory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewSpotTypeCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*if (!SpotType::whereName('event')->exists()) {
            $spotType = SpotType::create([
                'name' => 'event',
                'display_name' => 'Event'
            ]);
        }*/
        //else{
            $spotType = SpotType::whereName('event')->first();
        //}
        $spotTypeCategory = new SpotTypeCategory;
        $spotTypeCategory->name = 'ticketmaster';
        $spotTypeCategory->display_name = 'TicketMaster';
        $spotTypeCategory->spot_type_id = $spotType->id;
        $spotTypeCategory->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (SpotTypeCategory::whereName('ticketmaster')->exists()) {
            $spotTypeCategory = SpotTypeCategory::whereName('ticketmaster')->first();
            $spotTypeCategory->delete();
        }
    }
}
