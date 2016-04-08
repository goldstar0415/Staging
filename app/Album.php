<?php

namespace App;

use App\Extensions\GeoTrait;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Model Album
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
 *
 * Mutators properties
 * @property string $cover
 */
class Album extends BaseModel
{
    use PostgisTrait, GeoTrait;

    protected $appends = ['cover'];

    protected $fillable = [
        'title',
        'address',
        'location',
        'is_private'
    ];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    /**
     * Get cover url of the Album
     *
     * @return string
     */
    public function getCoverAttribute()
    {
        $cover = $this->photos()->first();
        $url = url('uploads/missings/covers');
        return $cover ? $cover->photo_url : [
            'original' => $url . '/original/missing.png',
            'medium' => $url . '/medium/missing.png',
            'thumb' => $url . '/thumb/missing.png',
        ];
    }

    /**
     * Get the user that owns the album.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the photos for the album.
     */
    public function photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }

    public function getCountPhotosAttribute()
    {
        return $this->photos()->count();
    }
}
