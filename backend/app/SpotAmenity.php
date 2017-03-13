<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotAmenity
 * @package App
 *
 * @property integer $id
 * @property integer $spot_id
 * @property string $title
 * @property string $item
 *
 * @property Hotel $hotel
 */
class SpotAmenity extends Model
{

    protected $fillable = ['title', 'item', 'spot_id'];

    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }
}
