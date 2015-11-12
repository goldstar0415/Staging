<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ImportLogRequest;
use App\Jobs\SpotsImport;
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * @var SpotsImportFile $import
         */
        $csv_file = $request->document->move(storage_path('csvs'), str_random(8) . '.' . $request->document->getClientOriginalExtension());
        $import = app(SpotsImportFile::class, [app(), app(Excel::class), $csv_file->getRealPath()]);

        if ($this->dispatch(new SpotsImport($import, ['admin' => $request->admin, 'spot_category' => $request->spot_category, 'document' => $csv_file->getRealPath()], SpotsImport::EVENT))) {
            return back()->with('import', true);
        }

        return back()->with('import', false);
    }

    public function getLog(ImportLogRequest $request)
    {
        switch ($request->type) {
            case 'event':
                return SpotsImport::getLog(SpotsImport::EVENT);
            case 'recreation':
                return SpotsImport::getLog(SpotsImport::RECREATION);
            case 'pitstop':
                return SpotsImport::getLog(SpotsImport::PITSTOP);
        }
    }

    public function deleteLog(ImportLogRequest $request)
    {
        $result = false;
        switch ($request->type) {
            case 'event':
                $result = SpotsImport::removeLog(SpotsImport::EVENT);
                break;
            case 'recreation':
                $result = SpotsImport::removeLog(SpotsImport::RECREATION);
                break;
            case 'pitstop':
                $result = SpotsImport::removeLog(SpotsImport::PITSTOP);
                break;
        }

        return back()->with('log_delete', $result);
    }
}
