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
        $response = $this->get('users/list/all');
        $this->assertResponseStatus(200);
    }

    public function testListFollowersNoAuth()
    {
        $response = $this->get('users/list/followers');
        $this->assertResponseStatus(401);
    }

    public function testListFollowingNoAuth()
    {
        $response = $this->get('users/list/followings');
        $this->assertResponseStatus(401);
    }

    public function testListFollowers()
    {
        $response = $this->get('users/list/followers');
        $this->markTestIncomplete();
        $this->assertResponseStatus(200);
    }

    public function testListFollowing()
    {
        $response = $this->get('users/list/followings');
        $this->markTestIncomplete();
        $this->assertResponseStatus(200);
    }

    public function testListAllFiltered()
    {
        $response = $this->get('users/list/all', ['filter' => 'something']);
        $this->assertResponseStatus(200);
    }

    public function testListFollowersFiltered()
    {
        $response = $this->get('users/list/followers', ['filter' => 'something']);
        $this->markTestIncomplete();
        $this->assertResponseStatus(401);
    }

    public function testListFollowingFiltered()
    {
        $response = $this->get('users/list/followings', ['filter' => 'something']);
        $this->markTestIncomplete();
        $this->assertResponseStatus(200);
    }
}
