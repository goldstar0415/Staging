<?php

namespace App;

use App\Extensions\Attachments;
use App\Scopes\NewestScopeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Request;

/**
 * Class Wall
 * @package App
 *
 * @property integer $id
 * @property integer $sender_id
 * @property integer $receiver_id
 * @property string $body
 *
 * Relation properties
 * @property SpotType $type
 * @property User $sender
 * @property User $receiver
 * @property \Illuminate\Database\Eloquent\Collection $spots
 * @property \Illuminate\Database\Eloquent\Collection $albumPhotos
 * @property \Illuminate\Database\Eloquent\Collection $areas
 */
class Wall extends BaseModel
{
    use SoftDeletes, Attachments, NewestScopeTrait;

    protected $fillable = ['body'];

    protected $dates = ['deleted_at'];

    protected $appends = ['rating', 'user_rating'];

    protected $with = ['sender'];

    public $exceptCacheAttributes = [
        'user_rating'
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->addAttachments();
    }

    /**
     * Get the user that receive the wall post
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the user that send the wall post
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the ratings for the wall post
     */
    public function ratings()
    {
        return $this->hasMany(WallRate::class);
    }

    /**
     * Get feeds where attached this wall post
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function feeds()
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    /**
     * Get the rating for the wall post
     *
     * @return int
     */
    public function getRatingAttribute()
    {
        return (int) $this->ratings()->sum('rate');
    }

    /**
     * Get rate of the authenticated user for the wall post
     *
     * @return int|null
     */
    public function getUserRatingAttribute()
    {
        if ($user = Request::user()) {
            $wall_rate = $this->ratings()->where('user_id', $user->id)->first();

            if ($wall_rate) {
                return $wall_rate->rate;
            } else {
                return 0;
            }
        }

        return null;
    }
}
