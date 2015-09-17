<?php

namespace App\Http\Controllers;

use App\Area;
use App\Http\Requests\Area\AreaStoreRequest;
use App\Http\Requests\Area\AreaRequest;
use App\Http\Requests\PaginateRequest;
use ChrisKonnertz\OpenGraph\OpenGraph;

use App\Http\Requests;

class AreaController extends Controller
{
    /**
     * AreaController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param PaginateRequest $request
     */
    public function index(PaginateRequest $request)
    {
        return $this->paginatealbe($request, $request->user()->areas());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AreaStoreRequest $request
     * @return Area
     */
    public function store(AreaStoreRequest $request)
    {
        $area = new Area($request->all());
        $request->user()->areas()->save($area);

        return $area;
    }

    /**
     * Display the specified resource.
     * @param Area $area
     * @return Area
     */
    public function show($area)
    {
        return $area;
    }

    /**
     * Display the specified resource preview.
     * @param Area $area
     * @return Area
     */
    public function preview($area)
    {
        $og = new OpenGraph();

        return view('opengraph')->with(
            'og',
            $og->title($area->title)
            ->image('')//TODO: change image
            ->description($area->description)
            ->url(config('app.frontend_url') . '/areas/' . $area->id)
        );//TODO: change frontend url
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AreaRequest $request
     * @param Area $area
     * @return Area
     */
    public function update(AreaRequest $request, $area)
    {
        $area->update($request->all());

        return $area;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AreaRequest $request
     * @param Area $area
     * @return array
     */
    public function destroy(AreaRequest $request, $area)
    {
        return ['result' => $area->delete()];
    }
}
