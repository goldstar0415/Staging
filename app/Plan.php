<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
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
 */
class Plan extends Model
{
    use PostgisTrait;

    protected $guarded = ['id', 'user_id'];

    protected $dates = ['start_date', 'end_date'];

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
}
