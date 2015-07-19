<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Role::class)->create();
        factory(Role::class, 'admin')->create();
        factory(Role::class, 'blogger')->create();
    }
}
