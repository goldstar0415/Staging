<?php

namespace App\Http\Controllers;

use App\ActivityLevel;
use App\Http\Requests\Admin\ActivityLevelRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ActivityLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $levels = ActivityLevel::all();

        return view('admin.activity_level.index')->with('levels', $levels);
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.activity_level.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ActivityLevelRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ActivityLevelRequest $request)
    {
        ActivityLevel::create($request->all());

        return redirect()->route('admin.activitylevel.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ActivityLevel $level
     * @return \Illuminate\View\View
     */
    public function edit($level)
    {
        return view('admin.activity_level.edit')->with('level', $level);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  ActivityLevel  $level
     * @return \Illuminate\View\View

     */
    public function update(ActivityLevelRequest $request, $level)
    {
        return view('admin.activity_level.edit')->with('level', $level);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ActivityLevel  $level
     * @return \Illuminate\Http\RedirectResponse

     */
    public function destroy($level)
    {
        $level->delete();

        return redirect()->route('admin.activitylevel.index');
    }
}
