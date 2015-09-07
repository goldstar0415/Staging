<?php

use App\Activity;
use App\ActivityCategory;
use App\Plan;
use Illuminate\Database\Seeder;

class ActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Plan::all()->each(function (Plan $plan) {
            $activities_count = mt_rand(1, 5);
            $positions = range(1, $activities_count);
            $activities = factory(Activity::class, $activities_count)->make();

            if ($activities instanceof Activity) {
                $activities->position = array_shift($positions);
                $activity_category = ActivityCategory::random()->first();
                $activities->plan()->associate($plan);
                $activities->category()->associate($activity_category);
                $plan->activities()->save($activities);
            } else {
                foreach ($activities as $activity) {
                    $activity->position = array_shift($positions);
                }
                $activities->each(function (Activity $activity) use ($plan) {
                    $activity_category = ActivityCategory::random()->first();
                    $activity->plan()->associate($plan);
                    $activity->category()->associate($activity_category);
                });
                $plan->activities()->saveMany($activities);
            }
        });
    }
}
