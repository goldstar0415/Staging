<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\SpotFilterRequest;
use App\Http\Requests\PaginateRequest;
use App\Spot;
use DB;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param PaginateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function index(PaginateRequest $request)
    {
        return view('admin.spots.index')->with('spots', $this->paginatealbe($request, Spot::query(), 15));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function emailSavers()
    {
//        Spot::

        return 'Ok';
    }

    /**
     * Display the specified resource.
     *
     * @param Requests\Admin\SearchRequest $request
     * @return \Illuminate\Http\Response
     */
    public function search(SearchRequest $request)
    {
        return view('admin.spots.index')->with('spots', Spot::search($request->search_text)->paginate());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param SpotFilterRequest $request
     * @return \Illuminate\Http\Response
     */
    public function filter(SpotFilterRequest $request)
    {
        $query = Spot::query();

        if ($request->has('filter.title')) {
            $query->where('title', 'ilike', '%' . $request->filter['title'] . '%');
        }
        if ($request->has('filter.description')) {
            $query->where('description', 'ilike', '%' . $request->filter['description'] . '%');
        }
        if ($request->has('filter.address')) {
            $query->whereHas('points', function ($query) use ($request) {
                $query->where('address', 'ilike', '%' . $request->filter['address'] . '%');
            });
        }
        if ($request->has('filter.username') or $request->has('filter.user_email')) {
            $query->whereHas('user', function ($query) use ($request) {
                if ($request->has('filter.username')) {
                    $query->search($request->filter['username']);
                }
                if ($request->has('filter.user_email')) {
                    $query->where('email', 'ilike', '%' . $request->filter['user_email'] . '%');
                }
            });
        }
        if ($request->has('filter.date')) {
            $query->where(function ($query) use ($request) {
                $query->where(DB::raw('start_date::date'), '<=', $request->filter['date'])
                    ->where(DB::raw('end_date::date'), '>=', $request->filter['date'])
                    ->whereNotNull('start_date');
            });
        }
        if ($request->has('filter.created_at')) {
            $query->where(DB::raw('created_at::date'), $request->filter['created_at']);
        }

        return view('admin.spots.index')->with('spots', $this->paginatealbe($request, $query, 15));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Spot  $spot
     * @return \Illuminate\Http\Response
     */
    public function destroy($spot)
    {
        $spot->delete();

        return back();
    }
}
