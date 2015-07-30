<?php

use App\Spot;
use App\SpotReview;
use App\User;
use Illuminate\Database\Seeder;

class SpotReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Spot::random(10)->get()->each(function (Spot $spot) {
            $reviews_count = mt_rand(2, 6);
            /**
             * @var \Illuminate\Database\Eloquent\Collection $users
             */
            $users = User::random($reviews_count)->get();
            $votes = factory(SpotReview::class, $reviews_count)
                ->make()
                ->each(
                    function (SpotReview $spot_review) use ($spot, $users) {
                        $spot_review->spot()->associate($spot);
                        $spot_review->user()->associate($users->shift());
                    }
                );
            $spot->votes()->saveMany($votes);
        });
    }
}
