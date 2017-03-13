<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MapSearchTest extends LaravelTestCase
{
    public function testTextSearchNoGPS()
    {
        $response = $this->get('/map/search', [
            'query' => 'fish'
        ]);
        $this->assertResponseOk();
    }

    public function testTextSearchGPS()
    {
        $response = $this->get('/map/search', [
            'query' => 'fish',
            'lat'   => '39.3',
            'lng'   => '-21.7',
        ]);
        $this->assertResponseOk();
    }

    public function testRadiusSelection()
    {
        $response = $this->get('/map/selection/radius', [
            'lat'    => '49.3',
            'lng'    => '-11.7',
            'radius' => '100000',
        ]);
        $this->assertResponseOk();
    }

    public function testPathSelection()
    {
        $response = $this->get('/map/selection/path', [
            'vertices' => [
                '0,20',
                '2,30',
                '10,25',
            ],
            'buffer' => 5000,
        ]);
        $this->assertResponseOk();
    }

    public function testLassoSelection()
    {
        $response = $this->get('/map/selection/lasso', [
            'vertices' => [
                '0,0',
                '0,31',
                '30,30',
                '30,0',
            ],
        ]);
        $this->assertResponseOk();
    }

    public function testList()
    {
        // undone

    }
}
