<?php

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
        $user = App\User::orderBy(DB::raw('RANDOM()'))->take(1)->first();
        $user->spots()->saveMany(
            factory(Spot::class, 25)->make()->each(function (Spot $spot) {
                $category = App\SpotTypeCategory::orderBy(DB::raw('RANDOM()'))->take(1)->first();
                $spot->category()->associate($category);
            })
        );
//        $spot->points()->saveMany(factory(App\SpotPoint::class, mt_rand(1, 10))->make());
//        $spot->tags()->saveMany(factory(App\Tag::class, mt_rand(1, 10))->make()->toArray());
    }
}
