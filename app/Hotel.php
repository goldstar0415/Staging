<?php

namespace App;

use App\Extensions\GeoTrait;
use App\RemotePhoto;
use App\HotelAmenity;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use DB;
use Request;

/**
 * Class Hotel
 * @package App
 *
 * @property integer $id
 * @property string  $title
 * @property string  $description
 * @property string  $hotels_url
 * @property string  $booking_url
 * @property Point   $location
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Hotel extends BaseModel
{
    use PostgisTrait, GeoTrait;
    
    protected $postgisFields = [
        'location' => Point::class,
    ];
    
    protected $guarded = ['id'];
    
    /**
     * Get remote photos
     */
    public function remotePhotos()
    {
        return $this->morphMany(RemotePhoto::class, 'associated');
    }
    
    /**
     * Get the votes for the spot
     */
    public function amenities()
    {
        return $this->hasMany(HotelAmenity::class);
    }
    
}
