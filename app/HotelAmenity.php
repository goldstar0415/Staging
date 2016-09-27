<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpotReport
 * @package App
 *
 * @property integer $id
 * @property integer $hotel_id
 * @property string $title
 * @property string $item
 *
 * @property Hotel $hotel
 */
class HotelAmenity extends Model
{

    protected $fillable = ['title', 'item', 'hotel_id'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
