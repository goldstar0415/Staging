<?php

namespace App;

/**
 * Class SpotVote
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_id
 * @property integer $vote
 *
 * Relation properties
 * @property Spot $spot
 * @property User $user
 */
class SpotVote extends BaseModel
{
    protected $fillable = ['vote'];

    /**
     * Get the spot that belongs to the vote
     */
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    /**
     * Get the user that made the vote
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
