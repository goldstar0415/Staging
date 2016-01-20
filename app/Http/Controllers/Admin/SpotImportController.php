<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ImportLogRequest;
use App\Http\Requests\Admin\SpotImportRequest;
use App\Jobs\SpotsImport;
use App\Jobs\SpotsImportColumns;
use App\Jobs\SpotsImportCsv;
use App\Services\SpotsImportFile;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Excel;

class SpotImportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.spot_import.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexColumns()
    {
        return view('admin.spot_import_columns.index');
    }

    public function storeColumns(Request $request)
    {

        $job = new SpotsImportColumns($request->all(), [
            'admin' => $request->user(),
            'spot_category' => $request->spot_category
        ], SpotsImport::EVENT);
        if ((bool)$request->preview) {
            $request->session()->flashInput($request->all());
            return view('admin.spot_import_columns.index')->with('spots', $job->getSpots());
        }

        return back()->with('import', $this->dispatch($job));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SpotImportRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SpotImportRequest $request)
    {
        /**
         * @var SpotsImportFile $import
         */
        $csv_file = $request->document->move(storage_path('csvs'), str_random(8) . '.' . $request->document->getClientOriginalExtension());
        $import = app(SpotsImportFile::class, [app(), app(Excel::class), $csv_file->getRealPath()]);

        if ($this->dispatch(new SpotsImportCsv($import, ['admin' => $request->user(), 'spot_category' => $request->spot_category, 'document' => $csv_file->getRealPath()], SpotsImportCsv::EVENT))) {
            return back()->with('import', true);
        }

        return back()->with('import', false);
    }

    public function getLog(ImportLogRequest $request)
    {
        switch ($request->type) {
            case 'event':
                return SpotsImportCsv::getLog(SpotsImportCsv::EVENT);
            case 'recreation':
                return SpotsImportCsv::getLog(SpotsImportCsv::RECREATION);
            case 'pitstop':
                return SpotsImportCsv::getLog(SpotsImportCsv::PITSTOP);
        }
    }

    public function deleteLog(ImportLogRequest $request)
    {
        $result = false;
        switch ($request->type) {
            case 'event':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::EVENT);
                break;
            case 'recreation':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::RECREATION);
                break;
            case 'pitstop':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::PITSTOP);
                break;
        }

        return back()->with('log_delete', $result);
    }
}
