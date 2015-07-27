<?php

namespace App;

use Carbon\Carbon;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

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
 * @property string $cover
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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
 *
 * Mutators properties
 * @property float $rating
 */
class Spot extends BaseModel implements StaplerableInterface
{
    use StaplerTrait;

    protected $guarder = ['id', 'user_id', 'spot_type_category_id'];

    protected $appends = ['rating'];

    protected $dates = ['start_date', 'end_date'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('cover');
        parent::__construct($attributes);
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = Carbon::createFromFormat($this->date_format, $value);
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = Carbon::createFromFormat($this->date_format, $value);
    }

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

    public function photos()
    {
        return $this->hasMany(SpotPhoto::class);
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class);
    }
}
