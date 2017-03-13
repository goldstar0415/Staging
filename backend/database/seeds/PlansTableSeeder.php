<?php

use App\Plan;
use App\User;
use Illuminate\Database\Seeder;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(7)->get()->each(function (User $user) {
            $plans = factory(Plan::class, mt_rand(1, 5))->make();
            if ($plans instanceof Plan) {
                $plans->user()->associate($user);
                $user->plans()->save($plans);
            } else {
                $plans->each(function (Plan $plan) use ($user) {
                    $plan->user()->associate($user);
                });
                $user->plans()->saveMany($plans);
            }
        });
    }
}
