<?php

namespace App;

/**
 * Class Following
 * @package App
 *
 * @property integer $follower_id
 * @property integer $following_id
 */
class Following extends BaseModel
{
    public $timestamps = false;

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
