<?php

namespace App;

/**
 * Class ChatMessage
 * @package App
 *
 * @property integer $id
 * @property string $body
 *
 * Relation properties
 * @property User $sender
 * @property User $receiver
 * @property \Illuminate\Database\Eloquent\Collection $albumPhotos
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

    public function albumPhotos()
    {
        return $this->belongsToMany(AlbumPhoto::class);
    }
}
