<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotVote
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_id
 * @property integer $vote
 */
class SpotVote extends Model
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
