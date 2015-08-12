<?php

namespace App\Http\Controllers;

use App\Activity;
use App\Http\Requests\Plan\PlanStoreRequest;
use App\Plan;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PlanController extends Controller
{
    /**
     * PlanController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(PlanStoreRequest $request)
    {
        $plan = new Plan($request->only('title', 'location', 'address'));
        if ($request->has('description')) {
            $plan->description = $request->input('description');
        }
        if ($request->has('start_date')) {
            $plan->start_date = $request->input('start_date');
        }
        if ($request->has('end_date')) {
            $plan->end_date = $request->input('end_date');
        }
        $request->user()->plans()->save($plan);

        if ($request->has('spots')) {
            $plan->spots()->sync($request->input('spots'));
        }
        if ($request->has('activities')) {
            foreach ($request->input('activities') as $activity_data) {
                $activity = new Activity($activity_data);
                $plan->activities()->save($activity);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
