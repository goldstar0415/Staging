<?php

namespace App;

/**
 * Class Favorite
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_id
 *
 * Relation properties
 * @property User $user
 * @property Spot $spot
 */
class Favorite extends BaseModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
