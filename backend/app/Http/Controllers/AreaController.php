<?php

namespace App\Http\Controllers;

use App\Area;
use App\Http\Requests\Area\AreaStoreRequest;
use App\Http\Requests\Area\AreaRequest;
use App\Http\Requests\PaginateRequest;
use ChrisKonnertz\OpenGraph\OpenGraph;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Requests;
use Illuminate\Http\Request;

/**
 * Class AreaController
 * @package App\Http\Controllers
 *
 * Saved areas resource controller
 */
class AreaController extends Controller
{
    /**
     * AreaController constructor.
     */
    public function __construct()
    {
        $this->middleware('base64upload:cover', ['only' => ['store', 'update']]);
        $this->middleware('auth', [
            'only' => [
                'store', 
                'update', 
                'destroy',
                //'show' // Uncomment if wanted to show only to authorized users
                ]
            ]
        );
    }

    /**
     * Display a listing of the saved areas.
     *
     * @param PaginateRequest $request
     */
    public function index(PaginateRequest $request)
    {
        return $this->paginatealbe($request, $request->user()->areas()->latest());
    }

    /**
     * Store a newly created area in storage.
     *
     * @param  AreaStoreRequest $request
     * @return Area
     */
    public function store(AreaStoreRequest $request)
    {
        $area = new Area($request->all());
        $area->hash = str_replace('.','', uniqid("", true));
        $request->user()->areas()->save($area);

        return $area;
    }

    /**
     * Display the specified area.
     * @param Request $request
     * @param Area $area
     * @return Area
     */
    public function show(Request $request, $area)
    {
        // Use this if needed to show only to owner
        /*if ($request->user()->id !== $area->user_id) {
            abort(403, 'Access denied');
        }*/
        return $area;
    }

    /**
     * The specified area preview.
     * @param Area $area
     * @return Area
     */
    public function preview($area) // Set $area_hash if using hash
    {
        // For hash using
        /*$area = Area::where('hash', $area_hash)->first();
        if(!$area)
        {
            throw new NotFoundHttpException;
        }*/
        $og = new OpenGraph();

        return view('opengraph')->with(
            'og',
            $og->title($area->title)
            ->description($area->description)
            ->image($area->cover->url())
            ->url(frontend_url('areas', $area->id))
        );
    }

    /**
     * Update the specified area in storage.
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
     * Remove the specified area from storage.
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
