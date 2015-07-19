<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class AlbumPhoto
 * @package App
 * 
 * @property integer $id
 * @property integer $album_id
 * @property string $address
 * @property Point $location
 *
 * Relation properties
 * @property Album $album
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $chatMessages
 */
class AlbumPhoto extends BaseModel
{
    use PostgisTrait;

    protected $postgisFields = [
        'location' => Point::class,
    ];

    protected $fillable = ['location', 'address'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function comments()
    {
        return $this->hasMany(AlbumPhotoComment::class);
    }

    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    public function chatMessages()
    {
        return $this->belongsToMany(ChatMessage::class);
    }
}
