<?php

namespace App;

/**
 * Class Feed
 * @package App
 *
 */
class Feed extends BaseModel
{
    protected $fillable = ['event_type'];

    protected $with = ['feedable', 'sender'];

    public function feedable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
