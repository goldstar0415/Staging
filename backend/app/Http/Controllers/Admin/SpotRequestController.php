<?php

namespace App\Http\Controllers\Admin;

use App\Events\OnSpotCreate;
use App\Events\OnSpotUpdate;
use App\Http\Requests\Admin\SearchRequest;
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
        return view('admin.spot_request.index')->with(
            'spots',
            Spot::withRequested()->where('is_approved', false)->where('is_private', false)
                ->paginate()
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \App\Spot $spot
     * @return \Illuminate\Http\Response
     */
    public function approve($spot)
    {
        $event = $spot->created_at == $spot->updated_at ? new OnSpotCreate($spot) : new OnSpotUpdate($spot);

        $spot->update(['is_approved' => true]);

        event($event);

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

    public function search(SearchRequest $request)
    {
        return view('admin.spot_request.index')
            ->with('spots', Spot::withRequested()
                ->where('is_approved', false)
                ->search($request->search_text)
                ->paginate());
    }
}
