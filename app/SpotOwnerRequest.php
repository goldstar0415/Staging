<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotOwnerRequest
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property string $url
 * @property string $text
 *
 * @property \App\User $user
 * @property \App\Spot $spot
 */
class SpotOwnerRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'url',
        'text'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
