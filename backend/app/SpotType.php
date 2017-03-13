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

    protected $ids = [];
        
    static function getTypeId($stringTypeName) 
    {
        if($type = SpotType::where('name', $stringTypeName)->first())
        {
            return $type->id;
        }
        throw new \Exception("Unknown spot type {$stringTypeName}");
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
    
    /**
     * Get list of categories with spot type
     */
    public static function categoriesList($typeName = null)
    {
        $categories = [];
        $query = self::with('categories');
        if($typeName)
        {
            $query->where('name', $typeName);
        }
        $query->get()->each(function (\App\SpotType $type) use (&$categories, $typeName) {
            $categories[$type->display_name] = $type->categories->pluck('display_name', 'id')->toArray();
        });
        return $categories;
    }
}
