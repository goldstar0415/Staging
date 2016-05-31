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

	static function getTypeId($stringTypeName) {
		switch($stringTypeName) {
			case 'shelter':
				return 1;
			case 'event':
				return 2;
			case 'todo':
				return 3;
			case 'food':
				return 4;
			case 'shelter':
				return 5;
			default:
				throw new \Exception("Unknown spot type {$stringTypeName}");
		}
	}
	
    /**
     * Get the category for the spot type
     */
    public function categories()
    {
        return $this->hasMany(SpotTypeCategory::class);
    }

    /**
     * Get the spots for the spot type
     */
    public function spots()
    {
        return $this->hasManyThrough(Spot::class, SpotTypeCategory::class);
    }
}
