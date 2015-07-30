<?php


namespace App\Extensions;

use Phaza\LaravelPostgis\Geometries\Point;

trait GeoTrait
{
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (isset($attributes['location'])) {
            $attributes['location'] = [
                'lat' => $attributes['location']->getLat(),
                'lng' => $attributes['location']->getLng()
            ];
        }

        return $attributes;
    }

    public function setLocationAttribute($value)
    {
        if ($value instanceof Point) {
            $this->attributes['location'] = $value;
        } elseif (is_array($value)) {
            $this->attributes['location'] = new Point($value['lat'], $value['lng']);
        } else {
            $this->attributes['location'] = null;
        }
    }
}