<?php

namespace App;

use App\Contracts\CalendarExportable;
use App\Contracts\Commentable;
use App\Extensions\Attachable;
use App\Extensions\StartEndDatesTrait;
use App\Scopes\ApprovedScopeTrait;
use App\Scopes\NewestScopeTrait;
use App\Services\SocialSharing;
use App\Spot;
use App\SpotVote;
use App\RemotePhoto;
use App\Extensions\Stapler\EloquentTrait as StaplerTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use DB;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Organizer;
use Request;
use Log;

/**
 * Class Spot
 * @package App
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $spot_type_category_id
 * @property string $title
 * @property string $description
 * @property array $web_sites
 * @property \Codesleeve\Stapler\Attachment $cover
 * @property array $videos
 * @property bool $is_approved
 * @property bool $is_private
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * Relation properties
 * @property User $user
 * @property SpotTypeCategory $category
 * @property \Illuminate\Database\Eloquent\Collection $walls
 * @property \Illuminate\Database\Eloquent\Collection $votes
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 * @property \Illuminate\Database\Eloquent\Collection $tags
 * @property \Illuminate\Database\Eloquent\Collection $plans
 * @property \Illuminate\Database\Eloquent\Collection $points
 * @property \Illuminate\Database\Eloquent\Collection $calendarUsers
 *
 * Mutators properties
 * @property float $rating
 * @property array $locations
 * @property string $type
 * @property \Illuminate\Database\Eloquent\Collection $comments_photos
 */
class SpotView extends BaseModel
{
    protected $table = 'mv_spots_spot_points';

    protected $appnds = ['cover'];
    public $timestamps = false;
    /**
     * Get the points for the spot
     */
    public function points()
    {
        return $this->hasMany(SpotPoint::class, 'spot_id');
    }
    
    public function votes()
    {
        return $this->hasMany(SpotVote::class, 'spot_id');
    }
    
    public function rating()
    {
        return $this->votes()
            ->selectRaw('avg(vote) as rating, spot_id')
            ->groupBy('spot_id');
    }
    
    public function getCoverAttribute()
    {
        $cover_url = null;
        // Cover from remote service
        $rph = RemotePhoto::where('associated_type', Spot::class)
                ->where('associated_id', $this->id)
                ->orderBy('image_type', 'desc')
                ->orderBy('created_at', 'asc')
                ->first();
        if( $rph )
        {
            $cover_url = $rph->url;
        }
        // Cover from booking (disabled)
        /*if( !$cover_url )
        {
            $spot = new Spot();
            $spot->id = $this->id;
            $spotInfo = $spot->getSpotExtension();
            $bookingUrl = (isset($spotInfo->booking_url))?$spot->getBookingUrl($spotInfo->booking_url):false;
            if(
                isset($spotInfo->booking_url) && 
                $spot->checkUrl($spotInfo->booking_url) && 
                $bookingUrl &&
                $bookingPageContent = $spot->getPageContent($bookingUrl, [
                    'headers' => $spot->getBookingHeaders()
                ])
            )
            {
                $cover_url = $result['cover_url'] = $spot->getBookingCover($bookingPageContent);
            }
        }*/
        // Cover from spot
        if ( empty($cover_url) ) {
            $spot = Spot::where('id', $this->id)->first();
            if($spot)
            {
                $cover = $spot->cover_url;
                $cover_url = $cover['thumb'];
            }
        }
        return $cover_url;
    }
        
        
}
