<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Seeds\FileSeeder;

class UserTableSeeder extends Seeder
{
    use FileSeeder;
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

        $zoomer = Role::take('zoomer');
        factory(User::class, 100)->create()->each(
            Closure::bind(
                function (User $user) use ($zoomer) {
                    $user->roles()->attach($zoomer);
                    $this->saveModelFile(
                        $user,
                        \Faker\Factory::create()->image(storage_path('app')),
                        'avatar'
                    );
                },
                $this
            )
        );
    }
}
