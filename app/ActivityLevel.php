<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model ActivityLevel
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property integer $favorites_count
 */
class ActivityLevel extends Model
{
    protected $fillable = ['name', 'favorites_count'];

    public $timestamps = false;
}
