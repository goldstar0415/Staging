<?php

namespace App\Http\Controllers;

use App\Http\Requests\Calendar\AddToCalendarRequest;
use App\Http\Requests\Calendar\GetCalendarPlansRequest;
use App\Http\Requests\Calendar\RemoveFromCalendarRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param \App\Spot $spot
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function add(AddToCalendarRequest $request, $spot)
    {
        $request->user()->calendarSpots()->attach($spot);

        return response('Ok');
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @param \App\Spot $spot
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function remove(RemoveFromCalendarRequest $request, $spot)
    {
        $request->user()->calendarSpots()->detach($spot);

        return response('Ok');
    }

    /**
     * Store a newly created resource in storage.
     * @param GetCalendarPlansRequest $request
     */
    public function getPlans(GetCalendarPlansRequest $request)
    {
        $user = $request->user();
        $result['spots'] = $user->calendarSpots()
            ->where('start_date', '>=', $request->get('start_date'))
            ->where('end_date', '<=', $request->get('end_date'))->get();
        $result['plans'] = $user->plans()
            ->where('start_date', '>=', $request->get('start_date'))
            ->where('end_date', '<=', $request->get('end_date'))->get();

        return $result;
    }
}
