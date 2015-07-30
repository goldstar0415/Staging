<?php

use App\Area;
use App\User;
use Illuminate\Database\Seeder;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(10)->get()->each(function (User $user) {
            $areas = factory(Area::class, mt_rand(2, 10))->make()->each(function (Area $area) use ($user) {
                $area->user()->associate($user);
            });
            $user->areas()->saveMany($areas);
        });
    }
}
