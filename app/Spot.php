<?php

namespace App;

/**
 * Class Spot
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_type_category_id
 * @property string $title
 * @property string $description
 * @property string $web_site
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 */
class Spot extends BaseModel
{
    protected $guarder = ['id', 'user_id', 'spot_type_category_id'];

    protected $dates = ['start_date', 'end_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    public function votes()
    {
        return $this->hasMany(SpotVote::class);
    }

    public function reviews()
    {
        return $this->hasMany(SpotReview::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category()
    {
        return $this->belongsTo(SpotTypeCategory::class);
    }

    public function points()
    {
        return $this->hasMany(SpotPoint::class);
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class);
    }
}
