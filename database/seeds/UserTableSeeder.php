<?php

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
        $admin = factory(App\User::class)->create([
                'first_name' => 'Admin',
                'last_name' => 'Administrator',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin')
            ]);
        $admin->roles()->attach(App\Role::take('admin'));

        $zoomer = App\Role::take('zoomer');
        factory(App\User::class, 100)->create()->each(function ($user) use ($zoomer) {
            $user->roles()->attach($zoomer);
        });
    }
}
