<?php

namespace App;

use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

/**
 * Model ActivityCategory
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $display_name
 * @property \Codesleeve\Stapler\Attachment $icon
 * @property string $icon_url
 *
 * Relation properties
 * @property \Illuminate\Database\Eloquent\Collection $activities
 */
class ActivityCategory extends BaseModel implements StaplerableInterface
{
    use StaplerTrait;

    protected $fillable = ['name', 'display_name', 'icon'];

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
    
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return $this->icon->url();
        }
        return null;
    }

    public function setIconPutAttribute($value)
    {
        if ($value) {
            $path = public_path('tmp/' . $value);
            $this->icon = $path;
        }
    }

    public function getIconPutAttribute()
    {
        //TODO: get icon for admin panel
    }
}
