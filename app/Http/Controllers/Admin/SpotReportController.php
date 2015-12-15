<?php

namespace App\Http\Controllers\Admin;

use App\SpotReport;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SpotReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('admin.spot_reports.index')->with('reports', $this->paginatealbe($request, SpotReport::query(), 15));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SpotReport  $report
     * @return \Illuminate\Http\Response
     */
    public function destroy($report)
    {
        $report->delete();

        return back();
    }
}
