<?php

namespace App;

use App\Contracts\Commentable;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;

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
class SpotPhoto extends BaseModel implements StaplerableInterface, Commentable
{
    use StaplerTrait;

    protected $fillable = ['photo'];

    protected $appends = ['photo_url', 'url'];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('photo', [
            'styles' => [
                'thumb' => [
                    'dimensions' => '70x70#',
                    'convert_options' => ['quality' => 100]
                ],
                'medium' => '160x160'
            ]
        ]);
        parent::__construct($attributes);
    }

    /**
     * Get 3 sizes urls of the photo
     *
     * @return array
     */
    public function getPhotoUrlAttribute()
    {
        return $this->getPictureUrls('photo');
    }
    
    /**
     * Get url of the photo
     *
     * @return array
     */
    public function getUrlAttribute()
    {
        return (isset($this->photo_url['original']))?$this->photo_url['original']:null;
    }

    /**
     * Get the spot that belongs to the photo
     */
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    /**
     * Get all of the comments for the photo
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * {@inheritDoc}
     */
    public function commentResourceOwnerId()
    {
        return $this->spot->user_id;
    }
}
