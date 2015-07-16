<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(RolesSeeder::class);
        $this->call(SpotTypesTableSeeder::class);
        if (App::environment('local')) {
            $this->call(UserTableSeeder::class);
            $this->call(SpotTypeCategoriesTableSeeder::class);
            $this->call(SpotTableSeeder::class);
            $this->call(SpotPointsTableSeeder::class);
            $this->call(TagsTableSeeder::class);
        }

        Model::reguard();
    }
}
