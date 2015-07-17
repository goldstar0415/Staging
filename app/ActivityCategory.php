<?php

namespace App;

/**
 * Class ActivityCategory
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $activities
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
