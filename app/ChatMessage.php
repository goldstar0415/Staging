<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChatMessage
 * @package App
 *
 * @property integer $id
 * @property string $body
 */
class ChatMessage extends Model
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
