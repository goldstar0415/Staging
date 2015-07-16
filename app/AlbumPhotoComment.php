<?php

namespace App;

/**
 * Class AlbumPhotoComment
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $album_photo_id
 * @property string $body
 */
class AlbumPhotoComment extends BaseModel
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
}
