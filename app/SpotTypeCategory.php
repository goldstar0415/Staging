<?php

namespace App;

/**
 * Class SpotTypeCategory
 * @package App
 *
 * @property integer $spot_type_id
 * @property string $name
 * @property string $display_name
 */
class SpotTypeCategory extends BaseModel
{
    protected $fillable = ['name', 'display_name'];

    public $timestamps = false;

    public function spot_type()
    {
        return $this->belongsTo(SpotType::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }
}
