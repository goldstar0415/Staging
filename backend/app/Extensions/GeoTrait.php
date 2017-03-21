<?php


namespace App\Extensions;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class GeoTrait
 * Use it for models which contains location property
 *
 * @package App\Extensions
 */
trait GeoTrait
{
    /**
     * {@inheritDoc}
     */
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

    public function getPointAttribute()
    {
        if (!is_null($this->location)) {
            return [
                'lat' => $this->location->getLat(),
                'lng' => $this->location->getLng()
            ];
        }

        return [
            'lat' => null,
            'lng' => null
        ];
    }

    /**
     * Location property mutator
     *
     * Save model's location
     *
     * @param Point|array $value
     */
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
