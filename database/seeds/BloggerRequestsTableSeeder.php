<?php

use App\BloggerRequest;
use App\User;
use Illuminate\Database\Seeder;

class BloggerRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(10)->get()->each(function (User $user) {
            $request = factory(BloggerRequest::class)->make();
            $user->bloggerRequest()->save($request);
        });
    }
}
