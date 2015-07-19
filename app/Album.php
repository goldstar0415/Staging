<?php

namespace App;


/**
 * Class Album
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property boolean $is_private
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $photos
 */
class Album extends BaseModel
{
    protected $fillable = ['name', 'is_private'];

    public $files_dir = 'user_rel/id';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }
}
