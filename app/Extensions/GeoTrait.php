<?php


namespace App\Extensions;


trait GeoTrait
{
    public function getLocationAttribute()
    {
        return ['lat' => $this->attributes['location']->getLat(), 'lng' => $this->attributes['location']->getLng()];
    }
}