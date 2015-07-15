<?php

use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Role::class)->create();
        factory(App\Role::class, 'admin')->create();
        factory(App\Role::class, 'blogger')->create();
    }
}
