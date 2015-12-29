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
        'description',
        'web_sites',
        'videos',
        'start_date',
        'end_date',
        'address',
        'location',
        'is_approved',
        'cover_file_name',
        'cover_file_size',
        'cover_content_type',
        'cover_updated_at'
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
        return $this->photos()->first()->photo_url;
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
}
