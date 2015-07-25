<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Social
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 */
class Social extends Model
{
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
