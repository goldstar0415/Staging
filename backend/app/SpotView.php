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
    protected $table = 'spots_mat_view';

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
    
    public static function saveView($id = null)
    {
        if($id)
        {
            DB::insert("INSERT INTO spots_mat_view SELECT * FROM
                spots_view WHERE id=?", [$id]);
        }
    }
    
    public static function updateView($id = null)
    {
        if($id)
        {
            self::deleteView($id);
            self::saveView($id);
        }
    }
    
    public static function deleteView($id = null)
    {
        if($id)
        {
            self::where('id', $id)->delete();
        }
    }
    
    public static function refreshView()
    {
        $sql = "
                TRUNCATE TABLE spots_mat_view;
                INSERT INTO spots_mat_view SELECT * FROM
                    spots_view;
        ";
        \DB::connection()->getPdo()->exec($sql);
    }
        
        
}
