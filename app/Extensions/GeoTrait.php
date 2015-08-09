<?php


namespace App\Extensions;

use Illuminate\Database\Query\Expression;
use Phaza\LaravelPostgis\Geometries\Point;

trait GeoTrait
{
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (isset($attributes['location'])) {
            $lat = 0.0;
            $lng = 0.0;
            if ($attributes['location'] instanceof Expression) {
                list($lng, $lat) = sscanf($attributes['location']->getValue(), "ST_GeogFromText('POINT(%f %f)')");
            } else {
                $lat = $attributes['location']->getLat();
                $lng = $attributes['location']->getLng();
            }
            $attributes['location'] = [
                'lat' => $lat,
                'lng' => $lng
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
