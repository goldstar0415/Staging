<?php

namespace App;

use Codesleeve\Stapler\ORM\EloquentTrait as StaplerTrait;
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
                'styles' => ['original' => '60x60']
            ]
        );
        parent::__construct($attributes);
    }

    public function getIconUrlAttribute()
    {
        return $this->icon->url();
    }

    public function type()
    {
        return $this->belongsTo(SpotType::class);
    }

    public function spots()
    {
        return $this->hasMany(Spot::class);
    }
}
