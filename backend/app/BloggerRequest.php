<?php

namespace App;

/**
 * Model BloggerRequest
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $status
 * @property string $text
 *
 * Relation properties
 * @property User $user
 */
class BloggerRequest extends BaseModel
{
    protected $fillable = ['status', 'text'];

    /**
     * Get the user that sends the request
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get avatar url of the user
     *
     * @return string
     */
    public function getUserAvatarAttribute()
    {
        return $this->user->avatar->url('medium');
    }
}
