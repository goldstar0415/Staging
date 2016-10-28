<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotRestaurant
 * @package App
 *
 * @property Spot $restaurant
 */
class SpotRestaurant extends Model
{
    
    protected $casts = [
        'hours' => 'array',
    ];

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    public function setHoursAttribute(array $value)
    {
        $this->attributes['hours'] = json_encode($value);
    }
}
