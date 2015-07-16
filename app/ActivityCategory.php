<?php

namespace App;

/**
 * Class ActivityCategory
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 */
class ActivityCategory extends BaseModel
{
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;
    
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
