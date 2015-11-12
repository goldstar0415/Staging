<?php

namespace App\Http\Controllers\Admin;

use App\BloggerRequest;
use App\Role;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class BloggerRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.blogger_request.index')->with('requests', BloggerRequest::query()->with('user')->paginate());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \App\BloggerRequest $request
     * @return \Illuminate\Http\Response
     */
    public function accept($request)
    {
        $request->update(['status' => 'accepted']);

        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\BloggerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function reject($request)
    {
        $request->update(['status' => 'rejected']);

        return back();
    }
}
