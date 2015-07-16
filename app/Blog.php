<?php

namespace App;

use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\MultiPoint;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class Blog
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $blog_category_id
 * @property string $title
 * @property string $body
 * @property string $address
 * @property Point $location
 * @property string $url
 * @property integer $count_views
 */
class Blog extends BaseModel
{
    use PostgisTrait;

    protected $guarded = ['id', 'user_id', 'count_views'];

    protected $postgisFields = [
        'b_box' => MultiPoint::class
    ];

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
