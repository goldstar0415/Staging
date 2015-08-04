<?php


class UserTest extends LaravelTestCase
{
    public function testGetMe()
    {
        $this->randomSignIn();
        $this->get('users/me')
            ->seeJson(
                [
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email
                ]
            );
    }

    public function testMeNotAuth()
    {
        $this->get('users/me')
            ->seeJson(
                [
                    'message' => 'user unauthorized',
                ]
            );
        $this->assertResponseStatus(401);
    }
}
