<?php

use App\SpotTypeCategory;
use App\User;
use Illuminate\Database\Seeder;
use App\Spot;

class SpotTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * @var App\User $user
         */
        $user = User::random()->first();
        $models = factory(Spot::class, 25)->make()->each(function (Spot $spot) {
            $category = SpotTypeCategory::random()->first();
            $spot->category()->associate($category);
        });
        $user->spots()->saveMany($models);
    }
}
