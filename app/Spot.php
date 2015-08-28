<?php

namespace App;

use App\Extensions\StartEndDatesTrait;
use App\Scopes\NewestScopeTrait;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Request;

/**
 * Class Spot
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_type_category_id
 * @property string $title
 * @property string $description
 * @property array $web_sites
 * @property string $cover
 * @property array $videos
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
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 * @property \Illuminate\Database\Eloquent\Collection $tags
 * @property \Illuminate\Database\Eloquent\Collection $plans
 * @property \Illuminate\Database\Eloquent\Collection $points
 *
 * Mutators properties
 * @property float $rating
 * @property array $locations
 */
class Spot extends BaseModel implements StaplerableInterface
{
    use StaplerTrait, StartEndDatesTrait, NewestScopeTrait;

    protected $guarded = ['id', 'user_id'];

    protected $appends = ['rating', 'cover_url', 'is_favorite', 'is_saved', 'is_rated'];

    protected $with = ['category.type', 'points', 'photos', 'user', 'comments'];

    protected $hidden = ['cover_file_name', 'cover_file_size', 'cover_content_type'];

    protected $casts = [
        'web_sites' => 'array',
        'videos' => 'array'
    ];

    protected $dates = ['start_date', 'end_date'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('cover');
        parent::__construct($attributes);
    }

    public function getCoverUrlAttribute()
    {
        return $this->getPictureUrls('cover');
    }

    public function getRatingAttribute()
    {
        return (float)$this->votes()->avg('vote');
    }

    public function getIsFavoriteAttribute()
    {
        if ($user = Request::user()) {
            return $user->favorites()->find($this->id) ? true : false;
        }

        return false;
    }

    public function getIsSavedAttribute()
    {
        if ($user = Request::user()) {
            return $user->calendarSpots()->find($this->id) ? true : false;
        }

        return false;
    }

    public function getIsRatedAttribute()
    {
        if ($user = Request::user()) {
            return SpotVote::where('spot_id', '=', $this->id)->where('user_id', '=', $user->id)->first() !== null;
        }

        return false;
    }

    public function setWebSitesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['web_sites'] = json_encode($value);
        }
    }

    public function setVideosAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['videos'] = json_encode($value);
        }
    }

    public function getLocationsAttribute()
    {
        return $this->points;
    }

    public function setTagsAttribute($value)
    {
        if (is_array($value)) {
            $tags_ids = [];

            foreach ($value as $tag) {
                $tags_ids[] = Tag::firstOrCreate(['name' => $tag])->id;
            }
            $this->tags()->sync($tags_ids);
        }
    }

    public function setLocationsAttribute($value)
    {
        if (is_array($value)) {
            $this->points()->delete();
            foreach ($value as $location) {
                $point = new SpotPoint();
                $point->location = $location['location'];
                $point->address = $location['address'];
                $this->points()->save($point);
            }
        }
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

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
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
