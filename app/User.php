<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 * Class User
 * @package App
 * 
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
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
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, EntrustUserTrait, PostgisTrait;

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
    protected $fillable = ['name', 'email', 'password'];

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

    public function followings()
    {
        return $this->hasMany(Following::class, 'follower_id');
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function chat_messages()
    {
        return $this->belongsToMany(ChatMessage::class, null, 'sender_id');
    }

    public function album_photo_comments()
    {
        return $this->hasMany(AlbumPhotoComment::class);
    }

    public function walls()
    {
        return $this->hasMany(Wall::class, 'sender_id');
    }

    public function friends()
    {
        return $this->hasMany(Friend::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function blog_comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function blogger_request()
    {
        return $this->hasOne(BloggerRequest::class);
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    public function spot_votes()
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

    public function spot_reviews()
    {
        return $this->hasMany(SpotReview::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }
}
