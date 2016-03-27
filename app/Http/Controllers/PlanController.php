<?php

namespace App\Http\Controllers;

use App\Activity;
use App\ActivityCategory;
use App\ChatMessage;
use App\Events\OnMessage;
use App\Http\Requests\Plan\PlanDestroyRequest;
use App\Http\Requests\Plan\PlanIndexRequest;
use App\Http\Requests\Plan\PlanInviteRequest;
use App\Http\Requests\Plan\PlanShowRequest;
use App\Http\Requests\Plan\PlanStoreRequest;
use App\Http\Requests\Plan\PlanUpdateRequest;
use App\Plan;

use App\Http\Requests;
use App\User;
use Illuminate\Http\Request;

/**
 * Class PlanController
 * @package App\Http\Controllers
 */
class PlanController extends Controller
{
    /**
     * PlanController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the plans.
     *
     * @param PlanIndexRequest $request
     */
    public function index(PlanIndexRequest $request)
    {
        return Plan::where('user_id', $request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))->latest()->paginate((int)$request->get('limit', 10));
    }

    /**
     * Store a newly created plan in storage.
     * @param PlanStoreRequest $request
     * @return Plan
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
            foreach ($request->input('spots') as $spot) {
                $plan->spots()->attach($spot['id'], ['position' => $spot['position']]);
            }
        }
        if ($request->has('activities')) {
            foreach ($request->input('activities') as $activity_data) {
                $activity = new Activity($activity_data);
                $plan->activities()->save($activity);
            }
        }

        return $plan;
    }

    /**
     * Display the specified plan.
     * @param PlanShowRequest $request
     * @param \App\Plan $plan
     * @return Plan
     */
    public function show(PlanShowRequest $request, $plan)
    {
        return $plan;
    }

    /**
     * Update the specified plan in storage.
     *
     * @param PlanUpdateRequest $request
     * @param  Plan $plan
     * @return Plan
     */
    public function update(PlanUpdateRequest $request, $plan)
    {
        $plan->title = $request->input('title');
        $plan->location = $request->input('location');
        $plan->address = $request->input('address');
        if ($request->has('description')) {
            $plan->description = $request->input('description');
        }
        if ($request->has('start_date')) {
            $plan->start_date = $request->input('start_date');
        }
        if ($request->has('end_date')) {
            $plan->end_date = $request->input('end_date');
        }
        $plan->save();

        if ($request->has('spots')) {
            $plan->spots()->detach();
            foreach ($request->input('spots') as $spot) {
                $plan->spots()->attach($spot['id'], ['position' => $spot['position']]);
            }
        }
        $plan->activities()->delete();
        if ($request->has('activities')) {
            foreach ($request->input('activities') as $activity_data) {
                $activity = new Activity($activity_data);
                $plan->activities()->save($activity);
            }
        }

        return $plan;
    }

    /**
     * Remove the specified plan from storage.
     *
     * @param PlanDestroyRequest $request
     * @param \App\Plan $plan
     * @return array
     */
    public function destroy(PlanDestroyRequest $request, $plan)
    {
        return ['result' => $plan->delete()];
    }

    /**
     * Get all activity categories
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getActivityCategories()
    {
        return ActivityCategory::all();
    }

    /**
     * Invite user(s) into the plan
     *
     * @param PlanInviteRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function invite(PlanInviteRequest $request)
    {
        $user = $request->user();
        foreach ($request->input('users') as $user_id) {
            $plan_id = (int)$request->input('plan_id');
            User::findOrFail($user_id)->invitedPlans()->attach($plan_id);

            $message = new ChatMessage(['body' => '']);
            $user->chatMessagesSend()->save($message, ['receiver_id' => $user_id]);
            $message->plans()->attach($plan_id);

            event(new OnMessage($user, $message, User::find($user_id)->random_hash));
        }

        return response('OK');
    }

    /**
     * Export the plan with iCalendar format
     *
     * @param Request $request
     * @param \App\Plan $plan
     * @return mixed
     */
    public function export(Request $request, $plan)
    {
        return response()->ical($plan);
    }
}
