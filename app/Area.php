<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Geometries\MultiPoint;


/**
 * Class Area
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $data
 * @property MultiPoint $b_box
 */
class Area extends Model
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
