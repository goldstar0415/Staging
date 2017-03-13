<?php

namespace App;

use App\Contracts\Commentable;
use App\Extensions\Attachable;
use App\Extensions\GeoTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;

/**
 * Model AlbumPhoto
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
class AlbumPhoto extends BaseModel implements StaplerableInterface, Commentable
{
    use PostgisTrait, StaplerTrait, GeoTrait, Attachable;

    protected $postgisFields = [
        'location' => Point::class,
    ];

    protected $with = ['album'];

    protected $appends = ['photo_url', 'url'];

    protected $fillable = ['photo', 'location', 'address'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('photo', [
            'styles' => [
                'medium' => '160x150#',
                'thumb' => ['dimensions' => '70x70', 'convert_options' => ['quality' => 100]]
            ]
        ]);
        parent::__construct($attributes);
    }

    /**
     * Get urls of 3 photo sizes
     *
     * @return array
     */
    public function getPhotoUrlAttribute()
    {
        return $this->getPictureUrls('photo');
    }
    
    /**
     * Get url of original photo
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return (isset($this->photo_url['original']))?$this->photo_url['original']:null;
    }

    /**
     * Get photo album
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * The comments that belong to the photo
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * The walls that belongs to the photo
     */
    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    /**
     * The chat messages that belongs to photo
     */
    public function chatMessages()
    {
        return $this->belongsToMany(ChatMessage::class);
    }

    /**
     * @inheritDoc
     */
    public function commentResourceOwnerId()
    {
        return $this->album->user_id;
    }
}
