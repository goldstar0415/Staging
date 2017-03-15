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
}
