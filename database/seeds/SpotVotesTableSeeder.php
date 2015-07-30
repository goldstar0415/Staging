<?php

use App\SpotVote;
use App\User;
use Illuminate\Database\Seeder;
use App\Spot;

class SpotVotesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Spot::all()->each(function (Spot $spot) {
            $votes_count = mt_rand(10, 20);
            /**
             * @var \Illuminate\Database\Eloquent\Collection $users
             */
            $users = User::random($votes_count)->get();
            $votes = factory(SpotVote::class, $votes_count)
                ->make()
                ->each(
                function (SpotVote $spot_vote) use ($spot, $users) {
                    $spot_vote->spot()->associate($spot);
                    $spot_vote->user()->associate($users->shift());
                }
            );
            $spot->votes()->saveMany($votes);
        });
    }
}
