<?php

use App\ActivityCategory;
use Illuminate\Database\Seeder;

class ActivityCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ActivityCategory::class, 20)->create();
    }
}
