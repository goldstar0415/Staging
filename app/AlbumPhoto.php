<?php

namespace App;

use App\Extensions\GeoTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;

/**
 * Class AlbumPhoto
 * @package App
 *
 * @property integer $id
 * @property integer $album_id
 * @property string $address
 * @property Point $location
 * @property \Codesleeve\Stapler\Attachment $photo
 *
 * Relation properties
 * @property Album $album
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $chatMessages
 *
 * Mutators properties
 * @property string $photo_url
 */
class AlbumPhoto extends BaseModel implements StaplerableInterface
{
    use PostgisTrait, StaplerTrait, GeoTrait;

    protected $postgisFields = [
        'location' => Point::class,
    ];

    protected $with = ['album'];

    protected $appends = ['photo_url'];

    protected $fillable = ['photo', 'location', 'address'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('photo');
        parent::__construct($attributes);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->getPictureUrls('photo');
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
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
