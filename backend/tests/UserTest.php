<?php


class UserTest extends LaravelTestCase
{
    public function testGetMe()
    {
        $this->randomSignIn();
        $response = $this->get('users/me');
        $this->seeJson([
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
        ]);
    }

    public function testMeNotAuth()
    {
        $response = $this->get('users/me');
        $this->seeJson(['message' => 'user unauthorized']);
        $this->assertResponseStatus(401);
    }

    public function testListAll()
    {
        $response = $this->get('users/list', ['type' => 'all']);
        $this->assertResponseStatus(200);
    }

    public function testListFollowers()
    {
        $response = $this->get('users/list', ['type' => 'followers']);
        $this->assertResponseStatus(200);
    }

    public function testListFollowing()
    {
        $response = $this->get('users/list', ['type' => 'following']);
        $this->assertResponseStatus(200);
    }

    public function testListAllFiltered()
    {
        $response = $this->get('users/list', ['type' => 'all', 'filter' => 'something']);
        $this->assertResponseStatus(200);
    }

    public function testListFollowersFiltered()
    {
        $response = $this->get('users/list', ['type' => 'followers', 'filter' => 'something']);
        $this->assertResponseStatus(200);
    }

    public function testListFollowingFiltered()
    {
        $response = $this->get('users/list', ['type' => 'following', 'filter' => 'something']);
        $this->assertResponseStatus(200);
    }
}
