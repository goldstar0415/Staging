<?php

namespace App;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;

/**
 * Class AlbumPhoto
 * @package App
 *
 * @property integer $id
 * @property \Codesleeve\Stapler\Attachment $photo
 *
 * Relation properties
 * @property Album $album
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \App\Spot $spot
 *
 * Mutators properties
 * @property string $photo_url
 */
class SpotPhoto extends BaseModel implements StaplerableInterface
{
    use StaplerTrait;
    
    protected $fillable = ['photo'];

    protected $appends = ['photo_url'];

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

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    public function comments()
    {
        return $this->morphMany(PhotoComment::class, 'commentable');
    }
}
