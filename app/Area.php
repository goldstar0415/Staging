<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\MultiPoint;

/**
 * Class Area
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $data
 * @property MultiPoint $b_box
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $walls
 */
class Area extends BaseModel
{
    use PostgisTrait;

    protected $fillable = ['data', 'b_box'];

    protected $postgisFields = [
        'b_box' => MultiPoint::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }
}
