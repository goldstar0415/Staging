<?php

use App\SpotType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewSpotType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        SpotType::whereName('recreation')->update([
            'name' => 'todo',
            'display_name' => 'To-Do'
        ]);
        SpotType::whereName('pitstop')->update([
            'name' => 'food',
            'display_name' => 'Food'
        ]);
        if (!SpotType::whereName('shelter')->exists()) {
            SpotType::create([
                'name' => 'shelter',
                'display_name' => 'Shelter'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SpotType::whereName('todo')->update([
            'name' => 'recreation',
            'display_name' => 'Recreation'
        ]);
        SpotType::whereName('food')->update([
            'name' => 'pitstop',
            'display_name' => 'Pitstop'
        ]);
        if (SpotType::whereName('shelter')->exists()) {
            SpotType::whereName('shelter')->delete();
        }
    }
}
