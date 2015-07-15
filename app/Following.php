<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Following
 * @package App
 *
 * @property integer $follower_id
 * @property integer $following_id
 */
class Following extends Model
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
