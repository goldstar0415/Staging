<?php

namespace App\Services;

use App\Plan;
use App\Spot;
use App\SpotPoint;
use App\User;
use DB;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;

class ICalendar
{
    const FILE_NAME = 'calendar_export.ics';

    protected $calendar;

    protected $events = [];

    const EXPORTABLE = ['plans', 'calendarSpots', 'invitedPlans'];

    /**
     * ICalendar constructor.
     */
    public function __construct()
    {
        $app_name = env('APP_NAME');
        $this->calendar = new Calendar($app_name);
        $this->getCalendar()->setMethod(Calendar::METHOD_PUBLISH);
        $this->calendar->setDescription(ucfirst($app_name) . ' Calendar');
    }

    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    public function makeForUser(User $user)
    {
        /**
         * @var \App\Spot $spot
         */
        foreach (Spot::exportable($user) as $event) {
            $this->calendar->addComponent($event);
        }
        foreach (Plan::exportable($user) as $event) {
            $this->calendar->addComponent($event);
        }
        foreach (User::exportable($user) as $event) {
            $this->calendar->addComponent($event);
        }

        return $this->getCalendar()->render();
    }
}
