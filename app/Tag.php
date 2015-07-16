<?php

namespace App;

/**
 * Class Tag
 * @package App
 *
 * @property int $id
 * @property string $name
 */
class Tag extends BaseModel
{
    protected $fillable = ['name'];

    public $timestamps = false;

    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }
}
