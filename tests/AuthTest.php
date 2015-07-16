<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var App\User
     */
    private $user;

    private $date_format;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->date_format = DB::getQueryGrammar()->getDateFormat();
        $this->user = factory(App\User::class)->make();
    }

    /**
     * A basic signUp test.
     */
    public function testSignUp()
    {
        $this->post('/users',
            [
                'first_name' => $this->user->getAttributeValue('first_name'),
                'last_name' => $this->user->getAttributeValue('last_name'),
                'email' => $this->user->getAttributeValue('email'),
                'password' => 'qwerty',
                'password_confirmation' => 'qwerty',
                '_token' => csrf_token()
            ]
        )->seeJson(
            [
                'id' => Auth::id(),
                'first_name' => $this->user->getAttributeValue('first_name'),
                'last_name' => $this->user->getAttributeValue('last_name'),
                'email' => $this->user->getAttributeValue('email'),
                'created_at' => Auth::user()->created_at->format($this->date_format),
                'updated_at' => Auth::user()->updated_at->format($this->date_format)
            ]
        );
        $this->assertTrue(Auth::user()->hasRole(config('entrust.default')));
    }

    public function testLogin()
    {
        $date_format = DB::getQueryGrammar()->getDateFormat();
        $this->post('/users/login',
            [
                'email' => $this->user->email,
                'password' => 'qwerty'
            ]
        )->seeJson(
            [
                'id' => Auth::id(),
                'first_name' => $this->user->getAttributeValue('first_name'),
                'last_name' => $this->user->getAttributeValue('last_name'),
                'email' => $this->user->getAttributeValue('email'),
                'created_at' => Auth::user()->created_at->format($this->date_format),
                'updated_at' => Auth::user()->updated_at->format($this->date_format)
            ]
        );
    }

}
