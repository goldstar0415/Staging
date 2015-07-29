<?php


namespace App\Extensions;


trait GeoTrait
{
    public function getLocationAttribute()
    {
        $location = $this->attributes['location'];

        if ($location)
        {
            return ['lat' => $location->getLat(), 'lng' => $location->getLng()];
        }

        return $location;
    }
}