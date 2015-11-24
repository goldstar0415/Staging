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

    /**
     * Get location points in bounding boxes
     *
     * @param array $b_boxes
     * @return mixed
     */
    public static function getInBBoxes(array $b_boxes)
    {
        $search_areas = [];
        foreach ($b_boxes as $b_box) {
            $search_areas[] = sprintf(
                '"location" && ST_MakeEnvelope(%s, %s, %s, %s, 4326) AND
                ("spots"."end_date" > NOW() AND "spots"."end_date" is not null OR "spots"."end_date" is null)',
                $b_box['_southWest']['lng'],
                $b_box['_southWest']['lat'],
                $b_box['_northEast']['lng'],
                $b_box['_northEast']['lat']
            );
        }
        $points = self::with(['spot', 'spot.user', 'spot.photos', 'spot.comments'])->select('spot_points.*')
            ->join('spots', function ($join) {
                /**
                 * @var JoinClause $join
                 */
                $join->on('spot_points.spot_id', '=', 'spots.id')->whereNull('spots.is_approved')->where('is_approved', '=' , true);
            })->whereRaw(implode(' OR ', $search_areas))->get();

        return $points;
    }
}
