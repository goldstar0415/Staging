<?php

use App\SpotTypeCategory;
use App\User;
use Illuminate\Database\Seeder;
use App\Spot;
use Seeds\FileSeeder;

class SpotTableSeeder extends Seeder
{
    use FileSeeder;
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
        $models->each(function (Spot $spot) {
            $this->saveModelFile(
                $spot,
                \Faker\Factory::create()->image(storage_path('app')),
                'cover'
            );
            for ($i = 0; $i < mt_rand(1, 5); ++$i) {
                $this->randomName()->saveModelFile(
                    $spot,
                    \Faker\Factory::create()->image(storage_path('app'))
                );
            }
        });
    }
}
