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
 *
 * Relation properties
 * @property User $user
 * @property SpotTypeCategory $category
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $votes
 * @property \Illuminate\Database\Eloquent\Collection $reviews
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 * @property \Illuminate\Database\Eloquent\Collection $tags
 * @property \Illuminate\Database\Eloquent\Collection $plans
 * @property \Illuminate\Database\Eloquent\Collection $points
 */
class Spot extends BaseModel
{
    protected $guarder = ['id', 'user_id', 'spot_type_category_id'];

    protected $appends = ['rating'];

    protected $dates = ['start_date', 'end_date'];

    public function getRatingAttribute()
    {
        return (float)$this->votes()->avg('vote');
    }

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
