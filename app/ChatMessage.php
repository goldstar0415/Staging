<?php

namespace App;

use App\Extensions\Attachments;
use App\Scopes\NewestScopeTrait;
use Carbon\Carbon;

/**
 * Model ChatMessage
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

    /**
     * Get pivot table for the chat message
     *
     * @return \Illuminate\Database\Eloquent\Relations\Pivot
     */
    public function getPivotAttribute()
    {
        return $this->sender()->first()->pivot;
    }

    /**
     * Get the sender for the chat message
     */
    public function sender()
    {
        return $this->belongsToMany(User::class, null, null, 'sender_id')
            ->withPivot('receiver_id', 'sender_deleted_at', 'receiver_deleted_at');
    }

    /**
     * Get the receiver for the chat message
     */
    public function receiver()
    {
        return $this->belongsToMany(User::class, null, null, 'receiver_id')
            ->withPivot('sender_id', 'sender_deleted_at', 'receiver_deleted_at');
    }

    /**
     * Get feed where attached this chat message
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function feeds()
    {
        return $this->morphMany(Feed::class, 'feedable');
    }

    /**
     * Soft delete message for the receiver
     *
     * @return int
     */
    public function deleteForReceiver()
    {
        return $this->pivot->update(['receiver_deleted_at' => Carbon::now()]);
    }

    /**
     * Soft delete message for the sender
     *
     * @return int
     */
    public function deleteForSender()
    {
        return $this->pivot->update(['sender_deleted_at' => Carbon::now()]);
    }
}
