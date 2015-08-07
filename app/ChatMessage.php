<?php

namespace App;

use App\Scopes\NewestScopeTrait;

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
 * @property \Illuminate\Database\Eloquent\Collection $spots
 * @property \Illuminate\Database\Eloquent\Collection $areas
 */
class ChatMessage extends BaseModel
{
    use NewestScopeTrait;

    protected $fillable = ['body'];

    protected $with = ['spots', 'albumPhotos', 'areas'];

    public function sender()
    {
        return $this->belongsToMany(User::class, null, null, 'sender_id')->withPivot('receiver_id');
    }

    public function receiver()
    {
        return $this->belongsToMany(User::class, null, null, 'receiver_id')->withPivot('sender_id');
    }

    public function albumPhotos()
    {
        return $this->belongsToMany(AlbumPhoto::class);
    }

    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }
}
