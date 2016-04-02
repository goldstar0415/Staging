<?php

namespace App;

/**
 * Model ActivityLevel
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property integer $favorites_count
 */
class ActivityLevel extends BaseModel
{
    protected $fillable = ['name', 'favorites_count'];

    public $timestamps = false;
}
