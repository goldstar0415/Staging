<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spot\SpotCategoriesRequest;
use App\Http\Requests\Spot\SpotDestroyRequest;
use App\Http\Requests\Spot\SpotStoreRequest;
use App\Http\Requests\Spot\SpotUpdateRequest;
use App\Spot;
use App\SpotType;
use Illuminate\Http\Request;

use App\Http\Requests;

class SpotController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth', ['except' => ['index', 'show']]);
//        $this->middleware('base64upload:cover', ['only' => ['store', 'update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        return $request->user()->spots;
    }

    /**
     * Store a newly created resource in storage.
     * @param SpotStoreRequest $request
     */
    public function store(SpotStoreRequest $request)
    {
        $spot = new Spot($request->except(['locations', 'tags', 'files']));

        $request->user()->spots()->save($spot);

        $spot->tags = $request->input('tags');
        $spot->locations = $request->input('locations');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $spot->photos()->create([
                    'photo' => $file
                ]);
            }
        }

        return $spot;
    }

    /**
     * Display the specified resource.
     *
     * @param  Spot $spot
     * @return $this
     */
    public function show($spot)
    {
        return $spot->load('photos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  SpotUpdateRequest $request
     * @param  \App\Spot $spot
     * @return Spot
     */
    public function update(SpotUpdateRequest $request, $spot)
    {
        $spot->update($request->except(['locations', 'tags', 'files']));

        $spot->tags = $request->input('tags');
        $spot->locations = $request->input('locations');

        if ($request->has('files')) {
            foreach ($request->file('files') as $file) {
                $spot->photos()->create([
                    'photo' => $file
                ]);
            }
        }

        return $spot;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param SpotDestroyRequest $request
     * @param Spot $spot
     * @return bool|null
     */
    public function destroy(SpotDestroyRequest $request, $spot)
    {
        return $spot->delete();
    }

    public function categories(SpotCategoriesRequest $request)
    {
        $type = $request->get('type');

        $type = SpotType::where('name', $type)->with('categories')->first();

        return $type->categories;
    }
}
