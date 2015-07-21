<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;


/**
 * Class Album
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property Point $location
 * @property string $address
 * @property boolean $is_private
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $photos
 */
class Album extends BaseModel
{
    use PostgisTrait;

    protected $guarded = ['id', 'user_id'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }
}
