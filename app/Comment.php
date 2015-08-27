<?php

namespace App;

/**
 * Class Comment
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $commentable_id
 * @property string $commentable_type
 * @property string $body
 *
 * Relation properties
 * @property AlbumPhoto $photo
 * @property User $user
 */
class Comment extends BaseModel
{
    protected $fillable = ['body'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }
}
