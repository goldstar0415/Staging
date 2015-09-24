<?php

namespace App;

use App\Extensions\Attachments;
use App\Scopes\NewestScopeTrait;
use Carbon\Carbon;

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
    use NewestScopeTrait, Attachments;

    protected $fillable = ['body'];

    protected $appends = ['pivot'];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->addAttachments();
    }


    public function getPivotAttribute()
    {
        return $this->sender()->first()->pivot;
    }
    
    public function sender()
    {
        return $this->belongsToMany(User::class, null, null, 'sender_id')
            ->withPivot('receiver_id', 'sender_deleted_at', 'receiver_deleted_at');
    }

    public function receiver()
    {
        return $this->belongsToMany(User::class, null, null, 'receiver_id')
            ->withPivot('sender_id', 'sender_deleted_at', 'receiver_deleted_at');
    }

    public function deleteForReceiver()
    {
        return $this->pivot->update(['receiver_deleted_at' => Carbon::now()]);
    }

    public function deleteForSender()
    {
        return $this->pivot->update(['sender_deleted_at' => Carbon::now()]);
    }
}
