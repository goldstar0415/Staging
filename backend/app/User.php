<?php

namespace App;

use App\Contracts\CalendarExportable;
use App\Extensions\GeoTrait;
use Carbon\Carbon;
use DB;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Organizer;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Request;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;
use App\SpotVote;
use Log;

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
 * @property string $vk_link
 * @property string $facebook_link
 * @property string $twitter_link
 * @property string $instagram_link
 * @property string $tumblr_link
 * @property string $google_link
 * @property string $custom_link
 * @property string $random_hash
 * @property string $token
 * @property string $alias
 * @property boolean $verified
 * @property \Carbon\Carbon $banned_at
 * @property string $ban_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $last_action_at
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
 * @property string $full_name
 */
class User extends BaseModel implements
    AuthenticatableContract,
    CanResetPasswordContract,
    StaplerableInterface,
    CalendarExportable
{
    use Authenticatable, CanResetPassword, EntrustUserTrait,
        PostgisTrait, StaplerTrait, GeoTrait {
        StaplerTrait::boot insteadof EntrustUserTrait;
        EntrustUserTrait::boot insteadof StaplerTrait;
        StaplerTrait::boot as bootStaplerT;
        EntrustUserTrait::boot as bootEntrustUserT;
    }

    const NOT_ALLOWED_ALIASES = [
        'logout',
        'list',
        'me'
    ];

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
        'ip',
        'created_at',
        'updated_at',
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
        'count_reviews',
        'is_following',
        'activity_level',
        'social_links'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'socials',
        'avatar_file_name',
        'avatar_file_size',
        'avatar_content_type',
        'vk_link',
        'facebook_link',
        'twitter_link',
        'instagram_link',
        'tumblr_link',
        'google_link',
        'custom_link',
        'is_hints',
	    'token',
	    'random_hash',
	    'ip',
	    'email',
	    'ban_reason',
	    'banned_at',
    ];

    protected $dates = ['deleted_at', 'banned_at', 'birth_date', 'last_action_at'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public static $aliasRule = '/(?=[a-zA-Z]+)\S*/';
    
    public $exceptCacheAttributes = [
        'can_follow',
        'is_following'
    ];

    protected $dummyAvatarUrlTemplate;

    /**
     * Scope a query to search by user full name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $filter
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $filter)
    {
        return $query
            ->where(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), 'like', "%$filter%")
            ->orWhere(DB::raw('LOWER(email)'), 'like', "%$filter%");
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar', [
            'styles' => [
                'thumb' => [
                    'dimensions' => '80x80#',
                    'convert_options' => ['quality' => 100]
                ],
                'medium' => '160x160#'
            ]
        ]);

        $adorableConfig = config('services.adorable');
        $this->dummyAvatarUrlTemplate = $adorableConfig['avatarUrlTemplate'];
        
        parent::__construct($attributes);
    }

    public function acceptCacheFlush()
    {
        if (count($dirty = $this->getDirty()) === 1 and array_has($dirty, 'last_action_at')) {
            return false;
        }
        
        return true;
    }
    /**
     * Set the user's birth date
     *
     * @param \Carbon\Carbon|string $value
     */
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

    /**
     * Get the user's activity level
     */
    public function getActivityLevelAttribute()
    {
        return ActivityLevel::where('favorites_count', '<', $this->favorites()->withoutNewest()->count())
            ->latest('favorites_count')->pluck('name');
    }

    public function getSocialLinksAttribute()
    {
        $links = [
            'vk_link',
            'facebook_link',
            'twitter_link',
            'instagram_link',
            'tumblr_link',
            'google_link',
            'custom_link'
        ];
        $result = [];
        foreach ($links as $link) {
            $result[substr($link, 0, strpos($link, '_link'))] = $this->$link;
        }

        return $result;
    }
    
    
    /**
     * Get the user's reviews count
     */
    public function getCountReviewsAttribute()
    {
        return SpotVote::where('user_id', $this->id)->count();
    }

    /**
     * Set user's banned at attribute
     *
     * @param bool $value
     */
    public function setBannedAtAttribute($value)
    {
        $value = (bool)$value;

        if (is_bool($value) and $value === true) {
            $this->attributes['banned_at'] = (string)Carbon::now();
        } else {
            $this->attributes['banned_at'] = null;
        }
    }

    /**
     * Get count of the user's favorites spots
     *
     * @return int
     */
    public function getCountFavoritesAttribute()
    {
        return $this->favorites()->withoutNewest()->count();
    }

    /**
     * Get count of the user's new messages
     *
     * @return int
     */
    public function getNewMessagesAttribute()
    {
        return $this->chatMessagesReceived()->withoutNewest()->where('is_read', false)->count();
    }

    /**
     * Get count of the user's photos
     *
     * @return int
     */
    public function getCountPhotosAttribute()
    {
        return $this->albums()->join('album_photos', 'album_photos.album_id', '=', 'albums.id')->count();
    }

    /**
     * Get ability of follow the user for the authenticated user
     *
     * @return bool
     */
    public function getCanFollowAttribute()
    {
        $user = Request::user();
        if (isset($user)) {
            return !$user->followings()->where('users.id', $this->id)->exists();
        }

        return false;
    }

    /**
     * Check the user is following for the authenticated user
     *
     * @return bool
     */
    public function getIsFollowingAttribute()
    {
        $user = Request::user();
        if (isset($user)) {
            return $this->followings()->find($user->id) ? true : false;
        }

        return false;
    }

    /**
     * Check user is registered via application
     *
     * @return bool
     */
    public function getIsRegisteredAttribute()
    {
        return isset($this->password);
    }

    public function setAliasAttribute($value)
    {
        if (!in_array($value, self::NOT_ALLOWED_ALIASES)) {
            $this->attributes['alias'] = $value;
        }
    }

    public function getGeoLocationAttribute()
    {
        $loc = '';
        if ($this->city) {
            $loc = $this->city;
        }
        if ($this->country) {
            $loc .= $loc ? ', ' . $this->country : $this->country;
        }

        return $loc ?: null;
    }

    /**
     * Get the user's attached socials
     *
     * @return array
     */
    public function getAttachedSocialsAttribute()
    {
        $socials = [];
        foreach ($this->socials->toArray() as $row) {
            $socials[] = $row['name'];
        }
        return $socials;
    }

    /**
     * Get urls of 3 avatar sizes
     *
     * @return array
     */
    public function getAvatarUrlAttribute()
    {
        $urls = $this->getPictureUrls('avatar');

        foreach ($urls as $k => $url) {
            if ( preg_match('/\/missing\.[pngjeif]{3,4}$/i', $url) ) {
                $urls[$k] = $this->getDummyAvatarUrl($k);
            }
        }

        return $urls;
    }

    protected function getDummyAvatarUrl($size)
    {
        switch ($size) {
            case 'medium': $size = 160; break;
            case 'thumb': $size = 70; break;
            default: $size = 256;
        }
        return str_replace([':size', ':identifier'], [$size, $this->id], $this->dummyAvatarUrlTemplate);
    }

    /**
     * Get the user's followers count
     *
     * @return int
     */
    public function getCountFollowersAttribute()
    {
        return $this->followers()->count();
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ($this->last_name ? ' ' . $this->last_name : '');
    }

    public function getIpAttribute($value)
    {
        return long2ip($value);
    }

    public function setIpAttribute($value)
    {
        $this->attributes['ip'] = ip2long($value);
    }

    /**
     * Get the user's spots count
     *
     * @return int
     */
    public function getCountSpotsAttribute()
    {
        return $this->spots()->withoutNewest()->count();
    }

    /**
     * Get the user's followings count
     *
     * @return int
     */
    public function getCountFollowingsAttribute()
    {
        return $this->followings()->count();
    }
    
    /**
     * Get user's favorite spots ids only
     * 
     * @return array
     */
    public function getFavoritesIdsAttribute()
    {
        $ids = DB::table('spot_user')->select(DB::raw('spot_id as id'))->where('user_id', $this->id)->get();
        return array_map(function($item) {
            return $item->id;
        }, $ids);
    }

    /**
     * Get the user's followers
     */
    public function followers()
    {
        return $this->belongsToMany(self::class, 'followings', 'following_id', 'follower_id')->withTimestamps();
    }

    /**
     * Get the user's followings
     */
    public function followings()
    {
        return $this->belongsToMany(self::class, 'followings', 'follower_id', 'following_id')->withTimestamps();
    }

    /**
     * Get the user's albums
     */
    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    /**
     * Get all the user's chat messages
     */
    public function chatMessages()
    {
        return DB::table('chat_messages')
            ->join('chat_message_user', 'chat_messages.id', '=', 'chat_message_user.chat_message_id')
            ->where(function ($query) {
                $query->where('sender_id', $this->id)->orWhere('receiver_id', $this->id);
            })->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get the user's received chat messages
     */
    public function chatMessagesReceived()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'receiver_id')->withPivot('sender_id');
    }

    /**
     * Get the user's sent chat messages
     */
    public function chatMessagesSend()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'sender_id')->withPivot('receiver_id');
    }

    /**
     * Get all of the album photos for the user.
     */
    public function albumPhotoComments()
    {
        return $this->morphMany(Comment::class, 'commentable', AlbumPhoto::class);
    }

    /**
     * Get the wall posts for the user
     */
    public function walls()
    {
        return $this->hasMany(Wall::class, 'receiver_id');
    }

    /**
     * The comments that belongs to the user
     */
    public function comments()
    {
        // reviews table is a relations table for comments
        return $this->belongsToMany(Comment::class, 'reviews', 'user_id', 'comment_id');
    }

    /**
     * Get friends of the user
     */
    public function friends() {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')->withTimestamps();;
    }

    /*
     * Get feeds for the user
     */
    public function feeds()
    {
        return $this->hasMany(Feed::class);
    }

    /**
     * Get saved areas of the user
     */
    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Get all of the blog comment's for the user.
     */
    public function blogComments()
    {
        return $this->morphMany(Comment::class, 'commentable', Blog::class);
    }

    /**
     * Get user's blogger request
     */
    public function bloggerRequest()
    {
        return $this->hasOne(BloggerRequest::class);
    }

    /**
     * Get user's blog posts
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Get user's votes on the spots
     */
    public function spotVotes()
    {
        return $this->hasMany(SpotVote::class);
    }

    /**
     * Get user's plans
     */
    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Get plans which invited the user
     */
    public function invitedPlans()
    {
        return $this->belongsToMany(Plan::class);
    }

    /**
     * Get user's favorite spots
     */
    public function favorites()
    {
        return $this->belongsToMany(Spot::class)->withTimestamps();
    }

    /**
     * Get the user's calendar spots
     */
    public function calendarSpots()
    {
        return $this->belongsToMany(Spot::class, 'calendar_spots')->withTimestamps();
    }

    /**
     * Get all of the user's spot comment's.
     */
    public function spotComments()
    {
        return $this->morphMany(Comment::class, 'commentable', Spot::class);
    }

    /**
     * Get the user's spots
     */
    public function spots()
    {
        return $this->hasMany(Spot::class);
    }

    /**
     * Get the socials that belongs to the user
     */
    public function socials()
    {
        return $this->belongsToMany(Social::class);
    }

    /**
     * Scope a query to only include users whose birthday tomorrow.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComingBirthday($query)
    {
        return $query->whereRaw("date_part('day', \"birth_date\") = date_part('day', CURRENT_DATE) + 1
             and date_part('month', \"birth_date\") = date_part('month', CURRENT_DATE)");
    }

    /**
     * {@inheritDoc}
     */
    public static function exportableEvents(User $user)
    {
        return self::whereRaw(self::exportableConditions())->get();
    }

    /**
     * {@inheritDoc}
     */
    public static function exportableConditions()
    {

        return "(date_part('month', \"birth_date\") > date_part('month', CURRENT_DATE)
                or date_part('day', \"birth_date\") >= date_part('day', CURRENT_DATE)
                and date_part('month', \"birth_date\") = date_part('month', CURRENT_DATE))";
    }

    /**
     * {@inheritDoc}
     */
    public static function exportable(User $user)
    {
        $users = self::exportableEvents($user);

        /**
         * @var \App\User $birth_user
         */
        foreach ($users as $birth_user) {
            yield self::makeVEvent($birth_user);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function export()
    {
        return self::makeVEvent($this);
    }

    /**
     * Make VEVENT according to iCalendar format
     *
     * @param self $user
     * @return Event
     */
    protected static function makeVEvent(self $user)
    {
        $ics_event = new Event($user->id);
        $full_name = $user->first_name . ' ' . $user->last_name;

        if ($user->description) {
            $ics_event->setDescription($user->description);
        }
        $ics_event->setDtStart($user->birth_date);
        $ics_event->setDtEnd($user->birth_date);
        if ($user->address) {
            $ics_event->setLocation($user->address);
        }
        $ics_event->setUseUtc(false);
        $ics_event->setOrganizer(new Organizer($full_name, ['email' => $user->email]));
        $ics_event->setSummary($full_name . ' birthday!!!');

        return $ics_event;
    }

    public function confirmEmail()
    {
        $this->verified = true;
        $this->token = null;

        $this->save();

        return $this;
    }

    /**
     * Relations which needs to flush from cache
     * @return array
     */
    public function flushRelations()
    {
        return [
            'invitedPlans',
            'favorites',
            'calendarSpots',
            'socials',
            'comments', // through 'reviews' table
            'roles'
        ];
    }
}
