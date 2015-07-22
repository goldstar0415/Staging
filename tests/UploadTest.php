<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UploadTest extends LaravelTestCase
{
    use DatabaseTransactions;

    /**
     * A basic upload test.
     */
    public function testUploadPhoto()
    {
        $this->post(
            '/',
            ['foo' => 'bar', 'baz' => 'maz'],
        ['Content-type' => 'multipart/form-data'])->attach(app_path('readme.md'), 'readme')
        ->see('Hello');

    }
}
