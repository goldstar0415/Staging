<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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
 */
class Activity extends Model
{
    use PostgisTrait;

    protected $dates = ['start_date', 'end_date'];

    protected $guarded = ['id', 'plan_id', 'activity_category_id'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
