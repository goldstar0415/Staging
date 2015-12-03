<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GeneratedUser
 * @package App
 *
 * @property User $user
 * @property string $password
 */
class GeneratedUser extends Model
{
    protected $fillable = ['password'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
