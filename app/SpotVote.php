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

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
