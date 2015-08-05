<?php

namespace App;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;

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
}
