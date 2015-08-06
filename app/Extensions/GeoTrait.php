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

    public static function getInBBoxes(array $b_boxes)
    {
        $search_areas = [];
        foreach ($b_boxes as $b_box) {
            $search_areas[] = sprintf(
                '"location" && ST_MakeEnvelope(%s, %s, %s, %s, 4326)',
                $b_box['_southWest']['lng'],
                $b_box['_southWest']['lat'],
                $b_box['_northEast']['lng'],
                $b_box['_northEast']['lat']
            );
        }
        $points = self::with('spot')->whereRaw(implode(' OR ', $search_areas))->get();

        return $points;
    }
}
