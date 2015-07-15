<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Favorite
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_id
 */
class Favorite extends Model
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
