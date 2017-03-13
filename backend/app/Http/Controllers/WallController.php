<?php

namespace App\Http\Controllers;

use App\Events\OnWallMessage;
use App\Events\OnWallPostDelete;
use App\Events\OnWallPostDislike;
use App\Events\OnWallPostLike;
use App\Http\Requests\Wall\WallDestroyRequest;
use App\Http\Requests\Wall\WallStoreRequest;
use App\Http\Requests\Wall\WallUpdateRequest;
use App\Http\Requests\WallIndexRequest;
use App\Services\Attachments;
use App\User;
use App\Wall;
use App\WallRate;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class WallController
 * @package App\Http\Controllers
 *
 * Wall resource controller
 */
class WallController extends Controller
{
    /**
     * @var Attachments
     */
    private $attachments;

    /**
     * WallController constructor.
     * @param Attachments $attachments
     */
    public function __construct(Attachments $attachments)
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->middleware('privacy', ['only' => ['index', 'like', 'dislike']]);
        $this->attachments = $attachments;
    }

    /**
     * Display a listing of the wall posts.
     * @param WallIndexRequest $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(WallIndexRequest $request)
    {
        return User::find($request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))
        ->walls()
        ->paginate((int)$request->get('limit', 10));
    }

    /**
     * Store a newly created wall post in storage.
     * @param WallStoreRequest $request
     * @return Wall
     */
    public function store(WallStoreRequest $request)
    {
        $receiver = User::findOrFail((int) $request->input('user_id'));

        $wall = new Wall(['body' => $request->input('message')]);
        $wall->sender()->associate($request->user());

        $receiver->walls()->save($wall);
        $this->attachments->make($wall);

        event(new OnWallMessage($wall));

        return $wall;
    }

    /**
     * Display the specified wall post.
     * @param Wall $wall
     * @return Wall
     */
    public function show($wall)
    {
        return $wall->load('sender', 'receiver');
    }

    /**
     * Update the specified wall post in storage.
     * @param WallUpdateRequest $request
     * @param Wall $wall
     * @return Wall
     */
    public function update(WallUpdateRequest $request, $wall)
    {

        $wall->body = $request->input('message');
        $wall->save();

        $this->attachments->make($wall);

        return $wall;
    }

    /**
     * Remove the specified wall post from storage.
     *
     * @param WallDestroyRequest $request
     * @param $wall
     * @return array
     */
    public function destroy(WallDestroyRequest $request, $wall)
    {
        event(new OnWallPostDelete($wall));

        return ['result' => $wall->delete()];
    }

    /**
     * Like specified wall post
     *
     * @param Request $request
     * @param Wall $wall
     * @return WallRate
     */
    public function like(Request $request, $wall)
    {
        $user = $request->user();
        /**
         * @var WallRate $wall_rate
         */
        $wall_rate = WallRate::where('user_id', $user->id)->where('wall_id', $wall->id)->first();

        if ($wall_rate === null) {
            $wall_rate = new WallRate(['rate' => 1]);
            $wall_rate->user()->associate($user);

            $wall->ratings()->save($wall_rate);
        } else {
            switch ($wall_rate->rate) {
                case 0:
                    $wall_rate->rate = 1;
                    break;
                case -1:
                    $wall_rate->rate = 0;
                    break;
            }
            $wall_rate->save();
        }

        event(new OnWallPostLike($wall, $wall_rate));

        return $wall_rate;
    }

    /**
     * Dislike specified wall post
     *
     * @param Request $request
     * @param Wall $wall
     * @return WallRate
     */
    public function dislike(Request $request, $wall)
    {
        $user = $request->user();
        /**
         * @var WallRate $wall_rate
         */
        $wall_rate = WallRate::where('user_id', $user->id)->where('wall_id', $wall->id)->first();

        if ($wall_rate === null) {
            $wall_rate = new WallRate(['rate' => -1]);
            $wall_rate->user()->associate($user);

            $wall->ratings()->save($wall_rate);
        } else {
            switch ($wall_rate->rate) {
                case 0:
                    $wall_rate->rate = -1;
                    break;
                case 1:
                    $wall_rate->rate = 0;
                    break;
            }
            $wall_rate->save();
        }

        event(new OnWallPostDislike($wall, $wall_rate));

        return $wall_rate;
    }
}
