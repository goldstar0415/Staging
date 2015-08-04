<?php

namespace App;

use App\Extensions\GeoTrait;
use App\Extensions\StartEndDatesTrait;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class Activity
 * @package App
 *
 * @property integer $id
 * @property integer $plan_id
 * @property integer $activity_category_id
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

    protected $guarded = ['id', 'plan_id', 'activity_category_id'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function category()
    {
        return $this->belongsTo(ActivityCategory::class);
    }
}
