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
use App\Http\Requests\Spot\SpotOwnerRequest;
use App\Http\Requests\Spot\SpotRateRequest;
use App\Http\Requests\Spot\SpotReportRequest;
use App\Http\Requests\Spot\SpotStoreRequest;
use App\Http\Requests\Spot\SpotUnFavoriteRequest;
use App\Http\Requests\Spot\SpotUpdateRequest;
use App\Http\Requests\SpotExportRequest;
use App\Services\Privacy;
use App\Spot;
use App\SpotPhoto;
use App\SpotReport;
use App\SpotType;
use App\SpotVote;
use App\User;
use App\SpotOwnerRequest as SpotOwnerRequestModel;
use ChrisKonnertz\OpenGraph\OpenGraph;
use Illuminate\Http\Request;

use App\Http\Requests;

/**
 * Class SpotController
 * @package App\Http\Controllers
 *
 * Spot resource controller
 */
class SpotController extends Controller
{
    /**
     * SpotController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show', 'categories', 'favorites', 'preview', 'export']]);
        $this->middleware('base64upload:cover', ['only' => ['store', 'update']]);
        $this->middleware('privacy', ['except' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the spots.
     * @param SpotIndexRequest $request
     * @param Privacy $privacy
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(SpotIndexRequest $request, Privacy $privacy)
    {
        $user = $request->user();
        $user_id = (int)$request->get(
            'user_id',
            $user ? $user->id : null
        );
        $spots = null;
        if ($user and $user_id === $user->id) {
            $spots = Spot::withRequested();
        } else {
            $spots = Spot::query();
        }
        $target = User::find($user_id);

        $spots = $spots->where('user_id', $user_id);
        if ($user->id !== $user_id and !$privacy->hasPermission($target, $target->privacy_events)) {
            $spots = $spots->where('is_private', false);
        }
        $spots = $spots->with('comments');

        return $this->paginatealbe($request, $spots);
    }

    /**
     * Store a newly created spot in storage.
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
        if ($spot->is_private) {
            $spot->is_approved = true;
        }

        $request->user()->spots()->save($spot);
        if ($request->has('tags')) {
            $spot->tags = $request->input('tags');
        }
        $spot->locations = $request->input('locations');

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $spot->photos()->create([
                    'photo' => $file
                ]);
            }
        }

        if ($spot->is_approved) {
            event(new OnSpotCreate($spot));
        }

        return $spot;
    }

    /**
     * Display the specified spot.
     *
     * @param  Spot $spot
     * @return $this
     */
    public function show($spot)
    {
        return $spot
            ->load(['photos', 'user', 'tags', 'comments'])
            ->append(['count_members', 'members', 'comments_photos']);
    }

    /**
     * Update the specified spot in storage.
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
            '_method',
            'is_private'
        ]));

        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $spot->cover = $cover->getRealPath();
        }

        if ($request->has('tags')) {
            $spot->tags = $request->input('tags');
        }
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

        if ($request->is_private != $spot->is_private) {
            if (!$request->is_private) {
                $spot->is_approved = false;
                $spot->is_private = false;
            } else {
                $spot->is_private = true;
                $spot->is_approved = true;
            }
            $spot->save();
        }

        if ($spot->is_approved) {
            event(new OnSpotUpdate($spot));
        }

        return $spot;
    }

    /**
     * Remove the specified spot from storage.
     *
     * @param SpotDestroyRequest $request
     * @param Spot $spot
     * @return bool|null
     */
    public function destroy(SpotDestroyRequest $request, $spot)
    {
        return ['result' => $spot->delete()];
    }

    /**
     * Get spots categories
     *
     * @param SpotCategoriesRequest $request
     * @return \Illuminate\Database\Eloquent\Collection|null|static[]
     */
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
     * Rate the spot
     *
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

    /**
     * Show favorites user spot
     *
     * @param Request $request
     * @return mixed
     */
    public function favorites(Request $request)
    {
        return User::find($request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))->favorites()->with('comments')->paginate((int)$request->get('limit', 10));
    }

    /**
     * Add the spot to favorites
     *
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
     * Remove the spot from favorites
     *
     * @param SpotUnFavoriteRequest $request
     * @param \App\Spot $spot
     * @return array
     */
    public function unfavorite(SpotUnFavoriteRequest $request, $spot)
    {
        $spot->favorites()->detach($request->user());

        return ['result' => true];
    }

    /**
     * Invite the user to the spot
     *
     * @param SpotInviteRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
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

    /**
     * The specified spot preview.
     * @param Spot $spot
     * @return Spot
     */
    public function preview($spot)
    {
        $og = new OpenGraph();

        return view('opengraph')->with(
            'og',
            $og->title($spot->title)
            ->image($spot->cover->url())//TODO: change image
            ->description($spot->description)
            ->url(config('app.frontend_url') . '/user/' . $spot->user_id . '/spots/' . $spot->id)
        );
    }

    /**
     * Get the spot members
     *
     * @param Request $request
     * @param \App\Spot $spot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function members(Request $request, $spot)
    {
        return $spot->calendarUsers;
    }

    /**
     * Export the spot
     * @param Request $request
     * @param \App\Spot $spot
     * @return
     */
    public function export(Request $request, $spot)
    {
        return response()->ical($spot);
    }

    /**
     * Create owner spot request
     * @param SpotOwnerRequest $request
     * @param \App\Spot $spot
     * @return SpotOwnerRequestModel
     */
    public function ownerRequest(SpotOwnerRequest $request, $spot)
    {
        $owner_request = new SpotOwnerRequestModel($request->except('spot_id'));
        $owner_request->spot()->associate($spot);
        $owner_request->user()->associate($request->user());
        $owner_request->save();

        return $owner_request;
    }

    /**
     * Report the spot
     *
     * @param SpotReportRequest $request
     * @param \App\Spot $spot
     * @return SpotReport
     */
    public function report(SpotReportRequest $request, $spot)
    {
        $report = new SpotReport();

        switch((int)$request->reason) {
            case 0:
                $report->reason = SpotReport::WRONG;
                break;
            case 1:
                $report->reason = SpotReport::INAPPROPRIATE;
                break;
            case 2:
                $report->reason = SpotReport::DUPLICATE;
                break;
            case 3:
                $report->reason = SpotReport::SPAM;
                break;
            case 4:
                $report->reason = SpotReport::OTHER;
                break;
            default:
                abort(403, 'Forbidden');
                break;
        }

        $report->text = $request->text;
        $report->spot()->associate($spot);

        $report->save();

        return $report;
    }
}
