<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = factory(User::class)->create([
                'first_name' => 'Admin',
                'last_name' => 'Administrator',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin')
            ]);
        $admin->roles()->attach(Role::take('admin'));

        if (App::environment('local')) {
            factory(User::class, 20)->create()->each(function (User $user) {
                $user->roles()->attach(Role::take('zoomer'));
            });
        }
    }
}
