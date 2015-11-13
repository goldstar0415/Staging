<?php

namespace App;

use App\Contracts\CalendarExportable;
use App\Contracts\Commentable;
use App\Extensions\StartEndDatesTrait;
use App\Scopes\ApprovedScopeTrait;
use App\Scopes\NewestScopeTrait;
use App\Services\SocialSharing;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use DB;
use Eluceo\iCal\Component\Event;
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
 * @property \Codesleeve\Stapler\Attachment $cover
 * @property array $videos
 * @property bool $is_approved
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
 * @property \Illuminate\Database\Eloquent\Collection $calendarUsers
 *
 * Mutators properties
 * @property float $rating
 * @property array $locations
 * @property string $type
 */
class Spot extends BaseModel implements StaplerableInterface, CalendarExportable, Commentable
{
    use StaplerTrait, StartEndDatesTrait, NewestScopeTrait, ApprovedScopeTrait;

    protected $guarded = ['id', 'user_id'];

    protected $appends = ['rating', 'cover_url', 'is_favorite', 'is_saved', 'is_rated', 'share_links'];

    protected $with = ['category.type', 'points', 'photos', 'user'];

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
     * Get urls of 3 cover sizes
     */
    public function getCoverUrlAttribute()
    {
        return $this->getPictureUrls('cover');
    }

    /**
     * Get spot rating
     *
     * @return float
     */
    public function getRatingAttribute()
    {
        return (float)$this->votes()->avg('vote');
    }

    /**
     * Check is spot favorite for authenticated user
     *
     * @return bool
     */
    public function getIsFavoriteAttribute()
    {
        if ($user = Request::user()) {
            return $user->favorites()->find($this->id) ? true : false;
        }

        return false;
    }

    /**
     * Check is spot saved to calendar for authenticated user
     *
     * @return bool
     */
    public function getIsSavedAttribute()
    {
        if ($user = Request::user()) {
            return $user->calendarSpots()->find($this->id) ? true : false;
        }

        return false;
    }

    /**
     * Check is the authenticated user appreciated the spot
     *
     * @return bool
     */
    public function getIsRatedAttribute()
    {
        if ($user = Request::user()) {
            return SpotVote::where('spot_id', '=', $this->id)->where('user_id', '=', $user->id)->first() !== null;
        }

        return false;
    }

    /**
     * Set the spot web sites
     *
     * @param array $value
     */
    public function setWebSitesAttribute(array $value)
    {
        $this->attributes['web_sites'] = json_encode($value);
    }

    /**
     * Set the spot videos
     *
     * @param array $value
     */
    public function setVideosAttribute(array $value)
    {
        $this->attributes['videos'] = json_encode($value);
    }

    /**
     * Get the spot locations
     */
    public function getLocationsAttribute()
    {
        return $this->points;
    }

    /**
     * Get the spot type
     *
     * @return mixed
     */
    public function getTypeAttribute()
    {
        return $this->category->type['name'];
    }

    /**
     * Set the spot tags
     *
     * @param array $value
     */
    public function setTagsAttribute(array $value)
    {
        $tags_ids = [];

        foreach ($value as $tag) {
            $tags_ids[] = Tag::firstOrCreate(['name' => $tag])->id;
        }
        $this->tags()->sync($tags_ids);
    }

    /**
     * Set the spot locations
     *
     * @param array $value
     */
    public function setLocationsAttribute(array $value)
    {
        $this->points()->delete();
        foreach ($value as $location) {
            $point = new SpotPoint();
            $point->location = $location['location'];
            $point->address = $location['address'];
            $this->points()->save($point);
        }
    }

    /**
     * Get count members of the spot
     *
     * @return int
     */
    public function getCountMembersAttribute()
    {
        return $this->calendarUsers()->count();
    }

    /**
     * Get the spot members
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMembersAttribute()
    {
        return $this->calendarUsers()
            ->orderBy(DB::raw(config('database.connections.' . config('database.default') . '.rand_func')))
            ->take(6)->get();
    }

    /**
     * Get the spot share links
     *
     * @return array
     */
    public function getShareLinksAttribute()
    {
        $url = url('spots', [$this->id, 'preview']);

        return [
            'facebook' => SocialSharing::facebook($url),
            'twitter' => SocialSharing::twitter($url),
            'google' => SocialSharing::google($url)
        ];
    }

    /**
     * Get the user that owns the spot
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The walls that belongs to the spot
     */
    public function walls()
    {
        return $this->belongsToMany(Wall::class);
    }

    /**
     * Get the votes for the spot
     */
    public function votes()
    {
        return $this->hasMany(SpotVote::class);
    }

    /**
     * Get all of the comments for the spot
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all users which mark as favorite the spot
     */
    public function favorites()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get the spot tags
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the spot category
     */
    public function category()
    {
        return $this->belongsTo(SpotTypeCategory::class);
    }

    /**
     * Get the points for the spot
     */
    public function points()
    {
        return $this->hasMany(SpotPoint::class);
    }

    /**
     * Get the photos for the spot
     */
    public function photos()
    {
        return $this->hasMany(SpotPhoto::class);
    }

    /**
     * The plans that belongs to the spot
     */
    public function plans()
    {
        return $this->belongsToMany(Plan::class);
    }

    /**
     * The users which added to calendar the spot
     */
    public function calendarUsers()
    {
        return $this->belongsToMany(User::class, 'calendar_spots')->withTimestamps();
    }

    /**
     * Scope a query to get only upcoming spots.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComing($query)
    {
        return $query->whereRaw("date_part('day', \"start_date\") = date_part('day', CURRENT_DATE) + 1
             and date_part('month', \"start_date\") = date_part('month', CURRENT_DATE)
             and date_part('year', \"start_date\") = date_part('year', CURRENT_DATE)");
    }

    /**
     * {@inheritDoc}
     */
    public static function exportableEvents(User $user)
    {
        return $user->calendarSpots()->where(...self::exportableConditions())->get();
    }

    /**
     * {@inheritDoc}
     */
    public static function exportableConditions()
    {
        return [
            'start_date',
            '>=',
            \DB::raw('NOW()')
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function exportable(User $user)
    {
        $spots = self::exportableEvents($user);

        /**
         * @var \App\Spot $spot
         */
        foreach ($spots as $spot) {
            yield self::makeVEvent($spot, $user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        return self::makeVEvent($this, $this->user);
    }

    /**
     * @param self $spot
     * @param User $user
     * @return Event
     */
    protected static function makeVEvent(self $spot, User $user)
    {
        $ics_event = new Event($spot->id);
        if ($spot->description) {
            $ics_event->setDescription($spot->description);
        }
        $ics_event->setDtStart($spot->start_date);
        $ics_event->setDtEnd($spot->end_date);
        if ($point = $spot->points()->first()) {
            $ics_event->setLocation($point->address);
        }
        if (!empty($spot->web_sites)) {
            $ics_event->setUrl($spot->web_sites[0]);
        }
        $ics_event->setUseUtc(false);
        $ics_event->setOrganizer($user->first_name . ' ' . $user->last_name, $user->email);
        $ics_event->setCategories($spot->category->display_name);
        $ics_event->setSummary($spot->title);

        return $ics_event;
    }

    /**
     * {@inheritDoc}
     */
    public function commentResourceOwnerId()
    {
        return $this->user_id;
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
        return $query
            ->whereRaw("(LOWER(\"title\") like LOWER('%$filter%') OR LOWER(\"description\") like LOWER('%$filter%'))");
    }

}
