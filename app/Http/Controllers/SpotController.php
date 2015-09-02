<?php

namespace App\Http\Controllers;

use App\ChatMessage;
use App\Events\OnMessage;
use App\Events\OnSpotCreate;
use App\Events\OnSpotUpdate;
use App\Http\Requests\Spot\SpotCategoriesRequest;
use App\Http\Requests\Spot\SpotDestroyRequest;
use App\Http\Requests\Spot\SpotFavoriteRequest;
use App\Http\Requests\Spot\SpotIndexRequest;
use App\Http\Requests\Spot\SpotInviteRequest;
use App\Http\Requests\Spot\SpotRateRequest;
use App\Http\Requests\Spot\SpotStoreRequest;
use App\Http\Requests\Spot\SpotUnFavoriteRequest;
use App\Http\Requests\Spot\SpotUpdateRequest;
use App\Spot;
use App\SpotPhoto;
use App\SpotType;
use App\SpotVote;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class SpotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show', 'categories', 'favorites']]);
        $this->middleware('base64upload:cover', ['only' => ['store', 'update']]);
        $this->middleware('privacy', ['except' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     * @param SpotIndexRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(SpotIndexRequest $request)
    {
        $spots = Spot::where('user_id', (int)$request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))->with('comments');

        if ($request->has('page') or $request->has('limit')) {
            return $spots->paginate((int)$request->get('limit', 10));
        }

        return $spots->get();
    }

    /**
     * Store a newly created resource in storage.
     * @param SpotStoreRequest $request
     * @return Spot
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

        event(new OnSpotCreate($spot));

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
        return $spot->load(['photos', 'points', 'tags', 'comments']);
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

        event(new OnSpotUpdate($spot));

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
        $type_categories = null;
        if ($request->has('type')) {
            $type_categories = SpotType::where('name', $request->get('type'))->with('categories')->first()->categories;
        } else {
            $type_categories = SpotType::with('categories')->get();
        }

        return $type_categories;
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
        return User::find($request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))->favorites()->paginate((int)$request->get('limit', 10));
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

    public function invite(SpotInviteRequest $request)
    {
        $user = $request->user();
        foreach ($request->input('users') as $user_id) {
            $message = new ChatMessage(['body' => '']);
            $user->chatMessagesSend()->save($message, ['receiver_id' => $user_id]);
            $message->spots()->attach((int) $request->input('spot_id'));

            event(new OnMessage($user, $message, User::find($user_id)->random_hash));
        }

        return response('Ok');
    }
}
