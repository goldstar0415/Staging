<?php

use App\SpotType;
use App\SpotTypeCategory;
use Illuminate\Database\Seeder;

class SpotDefaultCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SpotType::whereName('event')->first()->categories()->create([
            'name' => 'general',
            'display_name' => 'General'
        ]);
    }
}
