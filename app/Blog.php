<?php

namespace App;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class Blog
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
 * @property string $url
 * @property integer $count_views
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $category
 */
class Blog extends BaseModel implements StaplerableInterface
{
    use PostgisTrait, StaplerTrait;

    protected $guarded = ['id', 'user_id', 'blog_category_id', 'count_views'];

    protected $postgisFields = [
        'b_box' => Point::class
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('cover');
        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function category()
    {
        return $this->belongsTo(BlogCategory::class);
    }
}
