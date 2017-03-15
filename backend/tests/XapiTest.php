<?php


class XapiTest extends LaravelTestCase
{
    public function testWeatherDarkskyNoParams()
    {
        $response = $this->get('xapi/weather/darksky');
        $this->assertResponseStatus(422);
    }

    public function testWeatherDarksky()
    {
        $response = $this->get('xapi/weather/darksky', [
            'lat'    => '23',
            'lng'    => '56',
            'units'  => 'si',
            'lang'   => 'en',
            'extend' => 'hourly',

        ]);
        $this->assertResponseStatus(200);
    }

    public function testWeatherOpenWeatherMap()
    {
        $response = $this->get('xapi/weather/openweathermap', [
            'bbox' => '37.5538,55.7488,37.6510,55.7915,14',
            'cluster' => 'yes',
            'cnt' => 10,
            'units' => 'imperial',
        ]);
        $this->assertResponseStatus(200);
    }

    public function testGeocoderSearchAddressDetails()
    {
        $response = $this->get('xapi/geocoder/search', [
            'addressdetails' => '0', // todo: wtf is this?
            'q' => "chippew",
        ]);
        $this->markTestIncomplete();
        $this->assertResponseStatus(200);
    }
    public function testGeocoderSearchLimit()
    {
        $response = $this->get('xapi/geocoder/search', [
            'limit' => '10',
            'q' => "chippew",
        ]);
        $this->markTestIncomplete();
        $this->assertResponseStatus(200);
    }

    public function testGeocoderSearch()
    {
        $response = $this->get('xapi/geocoder/search', [
            'q' => "chippew",
        ]);
        $this->assertResponseStatus(200);
    }

    public function testGeocoderSearchNoQ()
    {
        $response = $this->get('xapi/geocoder/search', [
            // nada
        ]);
        $this->assertResponseStatus(422);
    }

    public function testGeocoderReverse()
    {
        $response = $this->get('xapi/geocoder/reverse', [
            'lat' => 38.897957,
            'lng' => 77.036560,
        ]);
        $this->assertResponseStatus(200);
    }

    public function testGeocoderReverseNoLatLng()
    {
        $response = $this->get('xapi/geocoder/reverse', [
            // nada
        ]);
        $this->assertResponseStatus(422);
    }
}


