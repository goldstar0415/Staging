<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotType
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 */
class SpotType extends Model
{
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;

    public function categories()
    {
        return $this->hasMany(SpotTypeCategory::class);
    }
}
