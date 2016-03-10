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
        User::random(10)->get()->each(function (User $user) {
            $models = factory(Spot::class, mt_rand(2, 5))->make()->each(function (Spot $spot) {
                $category = SpotTypeCategory::random()->first();
                $type = $category->type['name'];
                $spot->is_approved = true;
                $spot->category()->associate($category);
            });
            $user->spots()->saveMany($models);
        });
    }
}
