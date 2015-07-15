<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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
 */
class SpotPoint extends Model
{
    use PostgisTrait;

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
