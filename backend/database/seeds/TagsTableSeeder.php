<?php

use App\Spot;
use App\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = factory(Tag::class, 30)->create();
        Spot::all()->each(function (Spot $spot) use ($tags) {
            $rand = mt_rand(1, 5);
            if ($rand > 1) {
                $tags = $tags->random($rand)->map(function ($tag) {
                    return $tag->id;
                })->all();
            } else {
                $tags = $tags->random(1)->id;
            }
            $spot->tags()->attach($tags);
        });
    }
}
