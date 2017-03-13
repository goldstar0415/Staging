<?php

namespace App;

use App\Extensions\GeoTrait;
use App\Extensions\StartEndDatesTrait;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Model Activity
 * @package App
 *
 * @property integer $id
 * @property integer $plan_id
 * @property integer $activity_category_id
 * @property integer $position
 * @property string $title
 * @property string $description
 * @property string $address
 * @property Point $location
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 *
 * Relation properties
 * @property Plan $plan
 */
class Activity extends BaseModel
{
    use PostgisTrait, GeoTrait, StartEndDatesTrait;

    protected $dates = ['start_date', 'end_date'];

    protected $guarded = ['id', 'plan_id'];

    protected $with = ['category'];

    protected $hidden = ['activity_category_id'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    /**
     * Get the plan that belongs to the activity
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the category that belongs to the activity
     */
    public function category()
    {
        return $this->belongsTo(ActivityCategory::class);
    }
}
