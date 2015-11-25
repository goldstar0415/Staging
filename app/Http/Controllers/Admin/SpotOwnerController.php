<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PaginateRequest;
use App\SpotOwnerRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotOwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param PaginateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(PaginateRequest $request)
    {
        return view('admin.spots.owner')
            ->with('requests', $this->paginatealbe($request, SpotOwnerRequest::with(['user', 'spot']), 15));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \App\SpotOwnerRequest $owner_request
     * @return \Illuminate\Http\Response
     */
    public function accept($owner_request)
    {
        if ($owner_request->spot->hasOwner()) {
            abort(403, 'Spot already has an owner');
        }

        $owner_request->spot->user()->associate($owner_request->user)->save();
        $owner_request->delete();

        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\SpotOwnerRequest $owner_request
     * @return \Illuminate\Http\Response
     */
    public function reject($owner_request)
    {
        $owner_request->delete();

        return back();
    }
}
