<?php

namespace App;

use App\Extensions\GeoTrait;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class SpotPoint
 * @package App
 *
 * @property integer $id
 * @property integer $spot_id
 * @property string $address
 * @property Point $location
 *
 * Relation properties
 * @property Spot $spot
 */
class SpotPoint extends BaseModel
{
    use PostgisTrait, GeoTrait;

    protected $fillable = ['location', 'address'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public $timestamps = false;

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
