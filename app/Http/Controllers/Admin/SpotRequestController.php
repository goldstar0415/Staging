<?php

namespace App\Http\Controllers\Admin;

use App\Events\OnSpotCreate;
use App\Spot;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.spot_request.index')->with('spots', Spot::withRequested()->where('is_approved', false)->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \App\Spot $spot
     * @return \Illuminate\Http\Response
     */
    public function approve($spot)
    {
        $spot->update(['is_approved' => true]);

        event(new OnSpotCreate($spot));

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Spot $spot
     * @return \Illuminate\Http\Response
     */
    public function reject($spot)
    {
        $spot->delete();

        return back();
    }
}
