<?php

namespace App;

/**
 * Class SpotVote
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_id
 * @property integer $vote
 * @property text    $message
 *
 * Relation properties
 * @property Spot $spot
 * @property User $user
 */
class SpotVote extends BaseModel
{
    
    const TYPE_BOOKING     = 1;
    const TYPE_GOOGLE      = 2;
    const TYPE_FACEBOOK    = 3;
    const TYPE_YELP        = 4;
    const TYPE_HOTELS      = 5;
    const TYPE_TRIPADVISOR = 6;
    
    protected $fillable = [
        'vote', 
        'message', 
        'created_at',
        'remote_id',
        'remote_type',
        'remote_user_name',
        'remote_user_avatar',
        ];

    /**
     * Get the spot that belongs to the vote
     */
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    /**
     * Get the user that made the vote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getRemoteUserNameAttribute($value)
    {
        return self::remoteReviewerNameCheck($value);
    }
    
    public function getRemoteUserAvatarAttribute($value)
    {
        if(empty($value))
        {
            return url('uploads/missings/avatars/thumb/missing.png');
        }
        return $value;
    }
    
    public static function remoteReviewerNameCheck($value)
    {
        $depricatedNames = [
            'A Google User',
            'A TripAdvisor Member',
            'Путешественник',
            'Аноним',
            'A Traveler',
        ];
        
        if(in_array($value, $depricatedNames))
        {
            return 'Anonymous';
        }
        return $value;
    }
}
