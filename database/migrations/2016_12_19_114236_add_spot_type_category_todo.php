<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\SpotType;
use App\SpotTypeCategory;

class AddSpotTypeCategoryTodo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $type = SpotType::where('name', 'todo')->first();
        $query = SpotTypeCategory::where('name', 'todo');
        if(!$query->exists())
        {
            $category = new SpotTypeCategory();
            $category->name = 'todo';
            $category->display_name = 'ToDo';
            $type->categories()->save($category);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        SpotTypeCategory::where('name', 'todo')->delete();
    }
}
