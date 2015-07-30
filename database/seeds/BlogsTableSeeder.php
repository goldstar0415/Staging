<?php

use App\Blog;
use App\BlogCategory;
use App\User;
use Illuminate\Database\Seeder;

class BlogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::random(12)->get()->each(function (User $user) {
            $blogs = factory(Blog::class, mt_rand(1, 5))->make();
            if ($blogs instanceof Blog) {
                $blog_category = BlogCategory::random()->first();
                $blogs->user()->associate($user);
                $blogs->category()->associate($blog_category);
                $user->blogs()->save($blogs);
            } else {
                $blogs->each(function (Blog $blog) use ($user) {
                    $blog_category = BlogCategory::random()->first();
                    $blog->user()->associate($user);
                    $blog->category()->associate($blog_category);
                });
                $user->blogs()->saveMany($blogs);
            }
        });
    }
}
