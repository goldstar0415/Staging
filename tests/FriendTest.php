<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FriendTest extends LaravelTestCase
{
    public function testAddFriend()
    {
        $this->post('/', ['one' => 1], [], ['file1' => $this->makeUploadedFile()]);
    }
}
