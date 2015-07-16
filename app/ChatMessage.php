<?php

namespace App;

/**
 * Class ChatMessage
 * @package App
 *
 * @property integer $id
 * @property string $body
 */
class ChatMessage extends BaseModel
{
    protected $fillable = ['body'];

    public function sender()
    {
        return $this->belongsToMany(User::class, null, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsToMany(User::class, null, 'receiver_id');
    }

    public function album_photos()
    {
        return $this->belongsToMany(AlbumPhoto::class);
    }

}
