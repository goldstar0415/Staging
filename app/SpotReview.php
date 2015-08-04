<?php

namespace App;

/**
 * Class SpotReview
 * @package App
 *
 * @property integer $id
 * @property integer $spot_id
 * @property integer $user_id
 * @property string $body
 *
 * Relation properties
 * @property User $user
 * @property Spot $spot
 */
class SpotReview extends BaseModel
{
    protected $fillable = ['body'];

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
