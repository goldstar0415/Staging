<?php

use App\ActivityLevel;
use Illuminate\Database\Seeder;

class ActivityLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ActivityLevel::create([
            'name' => 'Low Activity',
            'favorites_count' => 0
        ]);

        ActivityLevel::create([
            'name' => 'Moderate',
            'favorites_count' => 11
        ]);

        ActivityLevel::create([
            'name' => 'Party animal',
            'favorites_count' => 51
        ]);
    }
}
