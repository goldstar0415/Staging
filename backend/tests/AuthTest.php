<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends LaravelTestCase
{
    use DatabaseTransactions;

    /**
     * @var App\User
     */

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
    }

    /**
     * A basic signUp test.
     */
    public function testSignUp()
    {
        $user = factory(App\User::class)->make();
        $response = $this->post('/users', [
            'first_name' => $user->getAttributeValue('first_name'),
            'last_name' => $user->getAttributeValue('last_name'),
            'email' => $user->getAttributeValue('email'),
            'password' => 'qwerty',
            'password_confirmation' => 'qwerty',
            '_token' => csrf_token()
        ]);

        $this->seeJson([
            'id' => Auth::id(),
            'first_name' => $user->getAttributeValue('first_name'),
            'last_name' => $user->getAttributeValue('last_name'),
            'email' => $user->getAttributeValue('email'),
            'created_at' => Auth::user()->created_at->format($this->date_format),
            'updated_at' => Auth::user()->updated_at->format($this->date_format)
        ]);

        $user = Auth::user();
        $this->assertTrue($user->hasRole(config('entrust.default')));
        $this->assertResponseStatus(200);
    }

    public function testLogin()
    {
        $user = factory(App\User::class)->create();
        $this->post('/users/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $this->seeJson([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'created_at' => $user->created_at->format($this->date_format),
            'updated_at' => $user->updated_at->format($this->date_format)
        ]);

        $this->assertResponseStatus(200);
    }

    public function testLoginError()
    {
        $response = $this->post('/users/login', [
            'email' => 'random@mail.com',
            'password' => 'randompassword'
        ]);

        $this->seeJson(['email' => 'These credentials do not match our records.']);
        $this->assertResponseStatus(422);
    }

    public function testIsGuest()
    {
        $user = App\User::random()->first();
        Auth::login($user);

        $response = $this->post('/users/login', [
            'email' => $user->email,
            'password' => 'randompassword'
        ]);

        $this->seeJson(['message' => 'user already authenticated']);
        $this->assertResponseStatus(400);
    }
}
