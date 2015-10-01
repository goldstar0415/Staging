<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WallRate
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $wall_id
 * @property integer $rate
 *
 * Relation properties
 * @property Wall $wall
 * @property User $user
 */

class WallRate extends Model
{
    protected $fillable = ['rate'];

    protected $casts = ['rate' => 'integer'];

    /**
     * Get the wall post that belongs to the wall rate
     */
    public function wall()
    {
        return $this->belongsTo(Wall::class);
    }

    /**
     * Get the user that owns the wall rate
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
