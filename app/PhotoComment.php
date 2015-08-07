<?php

namespace App;

/**
 * Class PhotoComment
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $album_photo_id
 * @property string $body
 *
 * Relation properties
 * @property AlbumPhoto $photo
 * @property User $user
 */
class PhotoComment extends BaseModel
{
    protected $fillable = ['body'];

    public function photo()
    {
        return $this->belongsTo(AlbumPhoto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commentable()
    {
        return $this->morphTo();
    }
}
