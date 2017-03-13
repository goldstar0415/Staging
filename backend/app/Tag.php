<?php

namespace App;

/**
 * Class Tag
 * @package App
 *
 * @property int $id
 * @property string $name
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $spots
 */
class Tag extends BaseModel
{
    protected $fillable = ['name'];

    public $timestamps = false;

    /**
     * The spots that belongs to the tag
     */
    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }
}
