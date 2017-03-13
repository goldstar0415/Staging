<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MapSearchTest extends LaravelTestCase
{

    public function testSearch()
    {
        $response = $this->post(
            '/map/search',
            [
                [
                    '_northEast' => ['lat' => 30.33333, 'lng' => 23.43434],
                    '_southWest' => ['lat' => 40.33333, 'lng' => 53.43434]
                ],
                [
                    '_northEast' => ['lat' => 30.33333, 'lng' => 23.43434],
                    '_southWest' => ['lat' => 40.33333, 'lng' => 53.43434]
                ],
                [
                    '_northEast' => ['lat' => 30.33333, 'lng' => 23.43434],
                    '_southWest' => ['lat' => 40.33333, 'lng' => 53.43434]
                ]
            ]
        );
        var_dump($this->response->getContent());
    }
}
