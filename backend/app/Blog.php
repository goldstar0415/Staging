<?php

namespace App;

use App\Contracts\Commentable;
use App\Extensions\GeoTrait;
use App\Scopes\NewestScopeTrait;
use App\Services\SocialSharing;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use App\Spot;

/**
 * Model Blog
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $blog_category_id
 * @property string $title
 * @property string $cover
 * @property string $body
 * @property string $address
 * @property Point $location
 * @property string $slug
 * @property integer $count_views
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $category
 */
class Blog extends BaseModel implements StaplerableInterface, Commentable
{
    use PostgisTrait, StaplerTrait, GeoTrait, NewestScopeTrait;

    protected $guarded = ['id', 'user_id', 'count_views'];

    protected $appends = ['cover_url', 'share_links'];

    /**
     * Field for postgis extension
     *
     * @var array
     */
    protected $postgisFields = [
        'location' => Point::class
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('cover', [
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
     * Get cover url of the Blog
     *
     * @return string
     */
    public function getCoverUrlAttribute()
    {
        return $this->getPictureUrls('cover');
    }

    /**
     * Get the user that owns the blog
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The comments that belong to the blog.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the category of the blog
     */
    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
    }
    
    /**
     * Get the related spot
     */
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    /**
     * Get url of the cover medium size
     *
     * @return string
     */
    public function getCoverLinkAttribute()
    {
        return $this->cover->url('medium');
    }

    /**
     * Set the blog's cover
     *
     * @param string $value
     */
    public function setCoverPutAttribute($value)
    {
        if ($value) {
            $path = public_path('tmp/' . $value);
            $this->cover = $path;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function commentResourceOwnerId()
    {
        return $this->user_id;
    }

    /**
     * Get social share links of the area
     *
     * @return array
     */
    public function getShareLinksAttribute()
    {
        $url = frontend_url('api', 'posts', $this->slug, 'preview');

        return [
            'facebook' => SocialSharing::facebook($url),
            'twitter' => SocialSharing::twitter($url),
            'google' => SocialSharing::google($url)
        ];
    }

    /**
     * Scope a query to search by blog text.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $filter)
    {
        return $query->whereRaw("LOWER(\"body\") like '%$filter%' OR LOWER(\"title\") like '%$filter%'");
    }
}
