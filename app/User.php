<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

/**
 * Class User
 * @package App
 * 
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $avatar
 * @property boolean $sex
 * @property \Carbon\Carbon $birth_date
 * @property string $address
 * @property Point $location
 * @property string $time_zone
 * @property string $description
 * @property integer $mail_events
 * @property integer $mail_favorites
 * @property integer $mail_followers
 * @property integer $mail_followings
 * @property integer $mail_wall
 * @property integer $mail_info
 * @property integer $mail_photo_map
 * @property boolean $notification_letter
 * @property boolean $notification_wall_post
 * @property boolean $notification_follow
 * @property boolean $notification_new_spot
 * @property boolean $notification_coming_spot
 * @property \Carbon\Carbon $banned_at
 * @property string $ban_reason
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Relation properties
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
 * @property \Illuminate\Database\Eloquent\Collection $spotReviews
 * @property \Illuminate\Database\Eloquent\Collection $spots
 * @property BloggerRequest $bloggerRequest
 */
class User extends BaseModel implements AuthenticatableContract, CanResetPasswordContract, StaplerableInterface
{
    use Authenticatable, CanResetPassword, EntrustUserTrait, PostgisTrait, EloquentTrait {
        EntrustUserTrait::boot insteadof EloquentTrait;
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
    protected $fillable = ['avatar', 'last_name', 'first_name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $dates = ['banned_at'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('avatar');
        parent::__construct($attributes);
    }

    public function followings()
    {
        return $this->hasMany(Following::class, 'follower_id');
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function chatMessages()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'sender_id');
    }

    public function chatMessagesReceived()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'receiver_id');
    }

    public function albumPhotoComments()
    {
        return $this->hasMany(AlbumPhotoComment::class);
    }

    public function walls()
    {
        return $this->hasMany(Wall::class, 'receiver_id');
    }

    public function friends()
    {
        return $this->hasMany(Friend::class);
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

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function spotReviews()
    {
        return $this->hasMany(SpotReview::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }

}
