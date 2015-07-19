<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use App\Services\Uploader\Download;
use App\Services\Uploader\Upload;
use Phaza\LaravelPostgis\Geometries\Point;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    protected $appends = ['photo_url'];

    public $files_dir = 'album_rel';

    public function setPhotoAttribute(UploadedFile $file)
    {
        /**
         * @var Upload $upload
         */
        $upload = app(Upload::class);
        $upload->make($file, $this)->save();
    }

    public function getPhotoUrlAttribute()
    {
        /**
         * @var Download $upload
         */
        $download = app(Download::class);
        return $download->link($this);
    }
    
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
