<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotHotel
 * @package App
 *
 * @property Hotel $hotel
 */
class SpotHotel extends Model
{

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
