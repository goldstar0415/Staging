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
        'is_approved',
        'cover_file_name',
        'cover_file_size',
        'cover_content_type',
        'cover_updated_at'
    ];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function getCoverAttribute()
    {
        return $this->photos()->first()->photo_url;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }
}
