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
 * @property boolean $is_private
 * @property string $address
 * @property Point $location
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $photos
 */
class Album extends BaseModel
{
    use PostgisTrait;

    protected $postgisFields = [
        'location' => Point::class,
    ];

    protected $fillable = ['name', 'is_private', 'location', 'address'];

    public $files_dir = 'user_rel/id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }
}
