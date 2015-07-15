<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class AlbumPhoto
 * @package App
 * 
 * @property integer $id
 * @property integer $album_id
 * @property string $address
 * @property Point $location
 */
class AlbumPhoto extends Model
{
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

    public function chat_messages()
    {
        return $this->belongsToMany(ChatMessage::class);
    }
}
