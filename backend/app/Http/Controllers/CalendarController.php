<?php

namespace App\Http\Controllers;

use App\Events\OnAddToCalendar;
use App\Http\Requests\Calendar\AddToCalendarRequest;
use App\Http\Requests\Calendar\GetCalendarPlansRequest;
use App\Http\Requests\Calendar\RemoveFromCalendarRequest;
use App\Services\ICalendar;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class CalendarController
 * @package App\Http\Controllers
 *
 * Calendar controller
 */
class CalendarController extends Controller
{
    /**
     * CalendarController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the saved to calendar spots.
     * @param AddToCalendarRequest|Request $request
     * @param \App\Spot $spot
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(AddToCalendarRequest $request, $spot)
    {
        $user = $request->user();
        $user->calendarSpots()->attach($spot);

        event(new OnAddToCalendar($user, $spot));

        return response('Ok');
    }

    /**
     * Remove the specified spot from the calendar.
     * @param RemoveFromCalendarRequest $request
     * @param \App\Spot $spot
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function remove(RemoveFromCalendarRequest $request, $spot)
    {
        $request->user()->calendarSpots()->detach($spot);

        return response('Ok');
    }

    /**
     * Show authenticated user plans
     *
     * @param GetCalendarPlansRequest $request
     */
    public function getPlans(GetCalendarPlansRequest $request)
    {
        $user = $request->user();

        $result['spots'] = $this->plansScope($request, $user->calendarSpots())->get();
        $result['plans'] = $this->plansScope($request, $user->plans())->get()->merge(
            $this->plansScope($request, $user->invitedPlans())->get()
        );

        return $result;
    }

    /**
     * Show plans in the specific interval
     *
     * @param Request $request
     * @param $query
     */
    private function plansScope(Request $request, $query)
    {
        return $query->where('start_date', '>=', $request->get('start_date'))
            ->where('end_date', '<=', $request->get('end_date'));
    }

    /**
     * Export the calendar into the ics format
     *
     * @param Request $request
     * @return mixed
     */
    public function export(Request $request)
    {
        return response()->ical($request->user());
    }
}
