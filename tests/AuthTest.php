<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testSignUp()
    {
        $this->put('/',
            [
                '_token' => csrf_token()
            ]
        )->seeJson(
            [
                'field' => 'value'
            ]
        );
    }

}
