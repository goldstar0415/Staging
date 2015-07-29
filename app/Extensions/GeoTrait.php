<?php


namespace App\Extensions;


trait GeoTrait
{
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if ($attributes['location']) {
            $attributes['location'] = [
                'lat' => $attributes['location']->getLat(),
                'lng' => $attributes['location']->getLng()
            ];
        }

        return $attributes;
    }
}