<?php

namespace App;

use App\Extensions\GeoTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Request;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;

/**
 * Class User
 * @package App
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property \Codesleeve\Stapler\Attachment $avatar
 * @property boolean $sex
 * @property \Carbon\Carbon $birth_date
 * @property string $address
 * @property Point $location
 * @property string $time_zone
 * @property string $description
 * @property integer $privacy_events
 * @property integer $privacy_favorites
 * @property integer $privacy_followers
 * @property integer $privacy_followings
 * @property integer $privacy_wall
 * @property integer $privacy_info
 * @property integer $privacy_photo_map
 * @property boolean $notification_letter
 * @property boolean $notification_wall_post
 * @property boolean $notification_follow
 * @property boolean $notification_new_spot
 * @property boolean $notification_coming_spot
 * @property string $random_hash
 * @property \Carbon\Carbon $banned_at
 * @property string $ban_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $followers
 * @property \Illuminate\Database\Eloquent\Collection $followings
 * @property \Illuminate\Database\Eloquent\Collection $albums
 * @property \Illuminate\Database\Eloquent\Collection $chatMessages
 * @property \Illuminate\Database\Eloquent\Collection $chatMessagesReceived
 * @property \Illuminate\Database\Eloquent\Collection $albumPhotoComments
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $friends
 * @property \Illuminate\Database\Eloquent\Collection $areas
 * @property \Illuminate\Database\Eloquent\Collection $blogComments
 * @property \Illuminate\Database\Eloquent\Collection $blogs
 * @property \Illuminate\Database\Eloquent\Collection $spotVotes
 * @property \Illuminate\Database\Eloquent\Collection $plans
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 * @property \Illuminate\Database\Eloquent\Collection $spotComments
 * @property \Illuminate\Database\Eloquent\Collection $spots
 * @property BloggerRequest $bloggerRequest
 *
 * Mutators properties
 * @property boolean $is_registered
 * @property array $attached_socials
 * @property string $avatar_url
 */
class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, StaplerableInterface
{
    use Authenticatable, CanResetPassword, EntrustUserTrait,
        PostgisTrait, StaplerTrait, SoftDeletes, GeoTrait {
        StaplerTrait::boot insteadof EntrustUserTrait;
        EntrustUserTrait::boot insteadof StaplerTrait;
        StaplerTrait::boot as bootStaplerT;
        EntrustUserTrait::boot as bootEntrustUserT;
    }

    /**
     * The "booting" method of the model.
     */
    public static function boot()
    {
        static::bootStaplerT();
        static::bootEntrustUserT();
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'avatar_file_name',
        'avatar_file_size',
        'avatar_content_type',
        'avatar_updated_at'
    ];

    protected $appends = [
        'avatar_url',
        'attached_socials',
        'is_registered',
        'can_follow',
        'count_followers',
        'count_followings',
        'count_spots',
        'is_following'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $with = ['socials'];

    protected $hidden = [
        'password',
        'remember_token',
        'socials',
        'avatar_file_name',
        'avatar_file_size',
        'avatar_content_type'
    ];

    protected $dates = ['deleted_at', 'banned_at', 'birth_date'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function scopeSearch($query, $filter)
    {
        return $query->whereRaw("LOWER(CONCAT(\"first_name\", ' ', \"last_name\")) like LOWER('%$filter%')");
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar');
        parent::__construct($attributes);
    }

    public function setBirthDateAttribute($value)
    {
        if (!$value instanceof Carbon) {
            $this->attributes['birth_date'] = $value ? Carbon::createFromFormat(
                config('app.date_format'),
                $value
            ) : $value;
        } else {
            $this->attributes['birth_date'] = $value;
        }
    }

    public function setBannedAtAttribute($value)
    {
        $value = (bool)$value;

        if (is_bool($value) and $value === true) {
            $this->attributes['banned_at'] = (string)Carbon::now();
        } else {
            $this->attributes['banned_at'] = null;
        }
    }

    public function getCountFavoritesAttribute()
    {
        return $this->favorites()->withoutNewest()->count();
    }

    public function getCountPhotosAttribute()
    {
        return $this->albums()->join('album_photos', 'album_photos.album_id', '=', 'albums.id')->count();
    }

    public function getCanFollowAttribute()
    {
        $user = Request::user();
        if (isset($user)) {
            return $user->followings()->find($this->id) ? false : true;
        }

        return false;
    }

    public function getIsFollowingAttribute()
    {
        $user = Request::user();
        if (isset($user)) {
            return $this->followers()->find($user->id) ? false : true;
        }

        return false;
    }

    public function getIsRegisteredAttribute()
    {
        return isset($this->password);
    }

    public function getAttachedSocialsAttribute()
    {
        $socials = [];
        foreach ($this->socials->toArray() as $row) {
            $socials[] = $row['name'];
        }
        return $socials;
    }

    public function getAvatarUrlAttribute()
    {
        return $this->getPictureUrls('avatar');
    }

    public function getCountFollowersAttribute()
    {
        return $this->followers()->count();
    }

    public function getCountSpotsAttribute()
    {
        return $this->spots()->withoutNewest()->count();
    }

    public function getCountFollowingsAttribute()
    {
        return $this->followings()->count();
    }

    public function followers()
    {
        return $this->belongsToMany(self::class, 'followings', 'following_id', 'follower_id')->withTimestamps();
    }

    public function followings()
    {
        return $this->belongsToMany(self::class, 'followings', 'follower_id', 'following_id')->withTimestamps();
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function chatMessages()
    {
        return DB::table('chat_messages')
            ->join('chat_message_user', 'chat_messages.id', '=', 'chat_message_user.chat_message_id')
            ->where(function ($query) {
                $query->where('sender_id', $this->id)->orWhere('receiver_id', $this->id);
            })->orderBy('created_at', 'DESC');
    }

    public function chatMessagesReceived()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'receiver_id')->withPivot('sender_id');
    }

    public function chatMessagesSend()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'sender_id')->withPivot('receiver_id');
    }

    public function albumPhotoComments()
    {
        return $this->morphMany(Comment::class, 'commentable', AlbumPhoto::class);
    }

    public function walls()
    {
        return $this->hasMany(Wall::class, 'receiver_id');
    }

    public function reviews()
    {
        return $this->belongsToMany(Comment::class, 'reviews');
    }

    public function friends()
    {
        return $this->hasMany(Friend::class);
    }

    public function feeds()
    {
        return $this->hasMany(Feed::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function blogComments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function bloggerRequest()
    {
        return $this->hasOne(BloggerRequest::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function spotVotes()
    {
        return $this->hasMany(SpotVote::class);
    }

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    public function invitedPlans()
    {
        return $this->belongsToMany(Plan::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(Spot::class)->withTimestamps();
    }

    public function calendarSpots()
    {
        return $this->belongsToMany(Spot::class, 'calendar_spots')->withTimestamps();
    }

    public function spotComments()
    {
        return $this->morphMany(Comment::class, 'commentable', Spot::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }

    public function socials()
    {
        return $this->belongsToMany(Social::class);
    }
}
