<?php

namespace App;

use App\Extensions\Stapler\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

/**
 * Class SpotTypeCategory
 * @package App
 *
 * @property integer $spot_type_id
 * @property string $name
 * @property string $display_name
 * @property \Codesleeve\Stapler\Attachment $icon
 * @property string $icon_url
 *
 * Relation properties
 * @property SpotType $type
 * @property \Illuminate\Database\Eloquent\Collection $spots
 */
class SpotTypeCategory extends BaseModel implements StaplerableInterface
{
    use StaplerTrait;

    protected $fillable = ['name', 'display_name', 'icon', 'spot_type_id'];

    protected $appends = ['icon_url'];

    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile(
            'icon',
            [
                'styles' => ['original' => '70x70']
            ]
        );
        parent::__construct($attributes);
    }

    /**
     * Get the category's icon url
     *
     * @return string
     */
    public function getIconUrlAttribute()
    {
        return $this->icon->url();
    }

    /**
     * Get category's type
     */
    public function type()
    {
        return $this->belongsTo(SpotType::class);
    }

    /**
     * Get the spots for the category
     */
    public function spots()
    {
        return $this->hasMany(Spot::class);
    }

    /**
     * Set the category's icon
     *
     * @param string $value
     */
    public function setIconPutAttribute($value)
    {
        if ($value) {
            $path = public_path('tmp/' . $value);
            $this->icon = $path;
        }
    }
    
    public function getPrefix()
    {
        return $this->type->name . '_' . $this->id . '_';
    }
    
    /**
     * Getting or creating spot category by name 
     * 
     * @param string $name
     * @param string $displayName
     * @param string $typeName
     * @return SpotTypeCategory
     */
    public static function getOrCreate($name, $displayName, $typeName = 'event')
    {
        $cat = SpotTypeCategory::where('name', $name);
        if($cat->exists())
        {
            $catObj = $cat->first();
        }
        else
        {
            $type = SpotType::getTypeId($typeName);
            $catObj = SpotTypeCategory::create([
                'name' => $name,
                'display_name' => $displayName,
                'spot_type_id' => $type
            ]);
        }
        return $catObj;
    }
    
    public static function getIconCache()
    {
        $cache = [];

        $spot_type_categories = self::select('id', 'spot_type_id')->get();
        foreach($spot_type_categories as $item) {
            $cache[$item->id] = $item->icon_url;
        }

        return $cache;
    }
}
