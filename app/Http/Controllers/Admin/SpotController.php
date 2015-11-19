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

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->has('description')) {
            $query->where('description', '%' . $request->description . '%');
        }
        if ($request->has('created_at')) {
            $query->where('created_at', $request->created_at);
        }
        if ($request->has('username') or $request->has('user_email')) {
            $query->whereHas('user', function ($query) use ($request) {
                if ($request->has('username')) {
                    $query->search($request->username);
                }
                if ($request->has('user_email')) {
                    $query->where('email', 'like', '%' . $request->user_email . '%');
                }
            });
        }
        if ($request->has('date')) {
            $query->where(function ($query) use ($request) {
                $query->where(DB::raw('start_date::date'), '<=', $request->date)
                    ->where(DB::raw('end_date::date'), '>=', $request->date)
                    ->whereNotNull('start_date');
            });
        }
        if ($request->has('created_at')) {
            $query->where('created_at', $request->created_at);
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
