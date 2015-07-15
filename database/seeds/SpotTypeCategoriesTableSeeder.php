<?php

use Illuminate\Database\Seeder;

class SpotTypeCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\SpotType::all()->each(function (App\SpotType $spot_type) {
            $spot_type->categories()->saveMany(factory(App\SpotTypeCategory::class, 20)->make());
        });
    }
}
