<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Album
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property boolean $is_private
 */
class Album extends Model
{
    protected $fillable = ['name', 'is_private'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }
}
