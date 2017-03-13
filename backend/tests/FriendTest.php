<?php

use App\Friend;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FriendTest extends LaravelTestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->randomSignIn();
    }

    public function testAddFriend()
    {
        /**
         * @var Friend $friend
         */
        $friend = factory(Friend::class)->make();

        $this->post(
            '/friends',
            [
                'first_name' => $friend->first_name,
                'last_name' => $friend->last_name,
                'birth_date' => $friend->birth_date,
//                'phone' => $friend->phone,
                'email' => $friend->email,
                'address' => $friend->address,
                'location' => ['lat' => $friend->location->getLat(), 'lng' => $friend->location->getLng()],
                'note' => $friend->note
            ]
        );
        $this->seeJson(['message' => 'Friend successfuly added']);
    }

    public function testUpdateFriend()
    {
        /**
         * @var Friend $friend
         */
        $friend = factory(Friend::class)->make();
        $old_friend = $this->user->friends()->random()->first();
        $this->put(
            '/friends/' . $old_friend->id,
            [
                'first_name' => $friend->first_name,
                'last_name' => $friend->last_name,
                'birth_date' => $friend->birth_date,
//                'phone' => $friend->phone,
                'email' => $friend->email,
                'address' => $friend->address,
                'location' => ['lat' => $friend->location->getLat(), 'lng' => $friend->location->getLng()],
                'note' => $friend->note
            ]
        );
        $this->seeJson(['message' => 'Friend successfuly updated']);
    }

    public function testDeleteFriend()
    {
        /**
         * @var Friend $friend
         */
        $old_friend = $this->user->friends()->random()->first();
        $this->delete('/friends/' . $old_friend->id);
        $this->seeJson(['message' => 'Friend successfuly deleted']);
    }

    public function testShowAllFriends()
    {
        $this->get('/friends');
        $this->see($this->user->friends->toJson());
    }
}
