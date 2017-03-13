<?php

use App\SpotType;
use App\SpotTypeCategory;
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
        SpotType::all()->each(function (SpotType $spot_type) {
            $spot_type->categories()->saveMany(factory(SpotTypeCategory::class, 10)->make());
        });
    }
}
