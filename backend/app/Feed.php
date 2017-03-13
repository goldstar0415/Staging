<?php

namespace App;

use App\Scopes\NewestScopeTrait;

/**
 * Model Feed
 * @package App
 *
 */
class Feed extends BaseModel
{
    use NewestScopeTrait;

    protected $fillable = ['event_type'];

    protected $with = ['feedable', 'sender'];

    /**
     * Get all of the owning feedable models.
     */
    public function feedable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the feed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that sent the feed
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
