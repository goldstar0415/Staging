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
class SpotToDo extends BaseModel
{
    protected $table = 'spot_todoes';
    
    /**
     * The spots that belongs to the tag
     */
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
