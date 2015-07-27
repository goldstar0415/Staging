<?php

namespace App;

use Carbon\Carbon;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;

/**
 * Class Plan
 * @package App
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property string $address
 * @property Point $location
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 *
 * Relation properties
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection $activities
 * @property \Illuminate\Database\Eloquent\Collection $spots
 */
class Plan extends BaseModel
{
    use PostgisTrait;

    protected $guarded = ['id', 'user_id'];

    protected $dates = ['start_date', 'end_date'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = Carbon::createFromFormat($this->date_format, $value);
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = Carbon::createFromFormat($this->date_format, $value);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function spots()
    {
        return $this->belongsToMany(Spot::class);
    }
}
