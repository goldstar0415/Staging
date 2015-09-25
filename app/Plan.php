<?php

namespace App;

use App\Contracts\CalendarExportable;
use App\Contracts\Commentable;
use App\Extensions\GeoTrait;
use App\Extensions\StartEndDatesTrait;
use Eluceo\iCal\Component\Event;
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
class Plan extends BaseModel implements CalendarExportable, Commentable
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
        return $this->belongsToMany(Spot::class)->withPivot('position');
    }

    public function invitedUsers()
    {
        return $this->belongsToMany(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * {@inheritdoc}
     */
    public static function exportableEvents(User $user)
    {
        return $user->plans()->where(...self::exportableConditions())->get()
            ->merge($user->invitedPlans()->where(...self::exportableConditions())->get());
    }

    /**
     * {@inheritdoc}
     */
    public static function exportableConditions()
    {
        return [
            'start_date',
            '>=',
            \DB::raw('NOW()')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function exportable(User $user)
    {
        $plans = self::exportableEvents($user);

        /**
         * @var \App\Plan $plan
         */
        foreach ($plans as $plan) {
            yield self::makeVEvent($plan, $plan->user);
        }
    }

    protected static function makeVEvent(self $plan, User $user)
    {
        $ics_event = new Event($plan->id);
        if ($plan->description) {
            $ics_event->setDescription($plan->description);
        }
        if ($plan->start_date) {
            $ics_event->setDtStart($plan->start_date);
            $ics_event->setDtEnd($plan->end_date);
        }
        $ics_event->setLocation($plan->address);
        $ics_event->setUseUtc();
        $ics_event->setOrganizer($user->first_name . ' ' . $user->last_name, $user->email);
        $ics_event->setSummary($plan->title);

        return $ics_event;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        return self::makeVEvent($this, $this->user);
    }

    /**
     * @inheritDoc
     */
    public function commentResourceOwnerId()
    {
        return $this->user_id;
    }
}
