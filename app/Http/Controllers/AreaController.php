<?php

namespace App\Http\Controllers;

use App\Area;
use App\Http\Requests\Area\AreaStoreRequest;
use App\Http\Requests\Area\AreaRequest;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Http\Request;

use App\Http\Requests;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        return $request->user()->areas;
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

        return $og->title($area->title)
                ->type('selection')
                ->image('http://blog.guybarrette.com/image.axd?picture=2012%2f8%2fSelection.jpg')//TODO: change image
                ->description($area->description)
                ->url(config('app.frontend_url') . '/selection/' . $area->id);//TODO: change frontend url
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
