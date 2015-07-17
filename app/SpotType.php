<?php

namespace App;

/**
 * Class SpotType
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $categories
 */
class SpotType extends BaseModel
{
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;

    public function categories()
    {
        return $this->hasMany(SpotTypeCategory::class);
    }
}
