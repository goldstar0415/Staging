<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spot\SpotCategoriesRequest;
use App\Http\Requests\Spot\SpotStoreRequest;
use App\Spot;
use App\SpotType;
use App\SpotTypeCategory;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotController extends Controller
{
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
     * @param SpotStoreRequest $request
     */
    public function store(SpotStoreRequest $request)
    {
        $tags = $request->input('tags');
        $locations = $request->input('locations');

        $spot = Spot::create($request->except(['locations', 'tags', 'files']));

        $spot->tags = $tags;
        $spot->locations = $locations;

        foreach ($request->file('files') as $file) {
            $spot->photos()->create([
                'photo' => $file
            ]);
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
        return $id;
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

    public function categories(SpotCategoriesRequest $request)
    {
        $type = $request->get('type');

        $type = SpotType::where('name', $type)->with('categories')->first();

        return $type->categories;
    }
}
