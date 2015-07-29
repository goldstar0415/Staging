<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FollowTest extends LaravelTestCase
{
    use DatabaseTransactions;
    /**
     * Setup the test environment.
     *
     * @return void
     */

    /**
     * @var User $user
     */
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = User::random()->first();
        Auth::login($this->user);
    }


    public function testFollow()
    {
        $user_id = $this->user->id;
        $follow_user = User::random()->whereDoesntHave('followers', function ($query) use ($user_id) {
            $query->where('follower_id', '=', $user_id);
        })->take(1)->first();

        $this->get('/follow/' . $follow_user->id)->seeJson(
            ['message' => 'You are successfuly follow user ' . $follow_user->first_name]
        );
        $this->assertResponseOk();
    }

    public function testUnfollow()
    {
        $user_id = $this->user->id;
        $unfollow_user = $this->user->followings()->random()->first();

        $this->get('/unfollow/' . $unfollow_user->id)->seeJson(
            ['message' => 'You are successfuly unfollow user ' . $unfollow_user->first_name]
        );
        $this->assertResponseOk();
    }
}
