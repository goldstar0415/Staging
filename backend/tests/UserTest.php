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
}
