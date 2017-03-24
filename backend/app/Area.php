<?php

namespace App;

use App\Extensions\Attachable;
use App\Services\SocialSharing;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;

/**
 * Model Area
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property string $data
 * @property string $b_box
 * @property string $waypoints
 * @property integer $zoom
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $walls
 */
class Area extends BaseModel implements StaplerableInterface
{
    use StaplerTrait, Attachable;

    protected $fillable = ['cover', 'title', 'description', 'data', 'waypoints', 'zoom', 'user_id'];

    protected $appends = ['share_links', 'cover_url'];

    protected $casts = [
        'data' => 'array',
        'waypoints' => 'array'
    ];

    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('cover');
        parent::__construct($attributes);
    }

    /**
     * Get the user that owns the area
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The walls that belongs to the area
     */
    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    /**
     * Get social share links of the area
     *
     * @return array
     */
    public function getShareLinksAttribute()
    {
        $url = frontend_url('api', 'areas', $this->id, 'preview');

        return [
            'facebook' => SocialSharing::facebook($url),
            'twitter' => SocialSharing::twitter($url),
            'google' => SocialSharing::google($url)
        ];
    }

    public function getCoverUrlAttribute()
    {
        return $this->getPictureUrls('cover');
    }
}
