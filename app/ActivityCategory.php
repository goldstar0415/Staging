<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityCategory
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 */
class ActivityCategory extends Model
{
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;
    
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
