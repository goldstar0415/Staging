<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spot\SpotCategoriesRequest;
use App\Http\Requests\Spot\SpotDestroyRequest;
use App\Http\Requests\Spot\SpotFavoriteRequest;
use App\Http\Requests\Spot\SpotRateRequest;
use App\Http\Requests\Spot\SpotStoreRequest;
use App\Http\Requests\Spot\SpotUnFavoriteRequest;
use App\Http\Requests\Spot\SpotUpdateRequest;
use App\Spot;
use App\SpotPhoto;
use App\SpotType;
use App\SpotVote;
use Illuminate\Http\Request;

use App\Http\Requests;

class SpotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->middleware('base64upload:cover', ['only' => ['store', 'update']]);
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
        $spot = new Spot($request->except(['locations', 'tags', 'files', 'cover']));
        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $spot->cover = $cover->getRealPath();
        }
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
        return $spot->load(['photos', 'points', 'tags']);
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
        $spot->update($request->except([
            'locations',
            'tags',
            'files',
            'cover',
            'deleted_files',
            '_method'
        ]));

        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $spot->cover = $cover->getRealPath();
        }

        $spot->tags = $request->input('tags');
        $spot->locations = $request->input('locations');

        $spot->save();

        $deleted_files = $request->input('deleted_files');

        if (!empty($deleted_files) and $spot->photos()->find($deleted_files)->count() === count($deleted_files)) {
            SpotPhoto::destroy($deleted_files);
        }

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
        return ['result' => $spot->delete()];
    }

    public function categories(SpotCategoriesRequest $request)
    {
        $type = $request->get('type');

        $type = SpotType::where('name', $type)->with('categories')->first();

        return $type->categories;
    }

    /**
     * @param SpotRateRequest $request
     * @param \App\Spot $spot
     * @return SpotVote
     */
    public function rate(SpotRateRequest $request, $spot)
    {
        $vote = new SpotVote($request->all());
        $vote->user()->associate($request->user());
        $spot->votes()->save($vote);

        return $vote;
    }

    public function favorites(Request $request)
    {
        return $request->user()->favorites;
    }

    /**
     * @param SpotFavoriteRequest $request
     * @param \App\Spot $spot
     * @return array
     */
    public function favorite(SpotFavoriteRequest $request, $spot)
    {
        $spot->favorites()->attach($request->user());

        return ['result' => true];
    }

    /**
     * @param SpotUnFavoriteRequest $request
     * @param \App\Spot $spot
     * @return array
     */
    public function unfavorite(SpotUnFavoriteRequest $request, $spot)
    {
        $spot->favorites()->detach($request->user());

        return ['result' => true];
    }
}
