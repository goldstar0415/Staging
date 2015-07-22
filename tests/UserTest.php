<?php


use App\User;

class UserTest extends LaravelTestCase
{
    public function testGetMe()
    {
        /**
         * @var User $user
         */
        $user = User::random()->first();
        Auth::login($user);
        $this->get('users/me')
            ->seeJson(
                [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email
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
