<?php

namespace App;

use App\Extensions\GeoTrait;
use App\Extensions\StartEndDatesTrait;
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
 * @property \Illuminate\Database\Eloquent\Collection $comments
 */
class Plan extends BaseModel
{
    use PostgisTrait, GeoTrait, StartEndDatesTrait;

    protected $guarded = ['id', 'user_id'];

    protected $dates = ['start_date', 'end_date'];

    protected $with = ['activities', 'spots'];

    protected $postgisFields = [
        'location' => Point::class,
    ];

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

    public function invitedUsers()
    {
        return $this->belongsToMany(User::class);
    }

    public function comments()
    {
        return $this->hasMany(PlanComment::class);
    }
}
