<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ImportLogRequest;
use App\Http\Requests\Admin\SpotImportRequest;
use App\Jobs\SpotsImportColumns;
use App\Jobs\SpotsImportCsv;
use App\Services\SpotsImportFile;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Vinkla\Instagram\InstagramManager;

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

    public function storeColumns(Request $request, InstagramManager $instagram)
    {
        if ($request->has('code') and $request->old('ins_photos')) {
            $request->replace($request->old());
        }
        if ($request->ins_photos) {
            $code = '';
            $data = null;

            if (empty($request->ins_token)) {
                \Session::flashInput($request->all());

                $code = $request->get('code');

                if (!$code) {
                    return redirect()->away($instagram->getLoginUrl(['basic', 'public_content']));
                }

                // Request the access token.
                $data = $instagram->getOAuthToken($code);
            } else {
                $data = $request->ins_token;
            }

            if (!$data) {
                return back(403)->withErrors('No valid access token found');
            }

            // Set the access token with $data object.
            $instagram->setAccessToken($data);
        }

        $job = new SpotsImportColumns($request->all(), [
            'admin' => [],
            'spot_category' => $request->spot_category,
            'instagram_photos' => $request->ins_photos,
            'get_address' => $request->get_address
        ], $request->spot_type);
        if ((bool)$request->preview) {
            $request->session()->flashInput($request->all());
            return view('admin.spot_import_columns.index')->with('spots', $job->getSpots());
        }

        return back()->with('import', $this->dispatch($job->onQueue(env('QUEUE_WORK_NAME', 'default'))));
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
		
        if ($this->dispatch((new SpotsImportCsv(['admin' => [], 'spot_category' => $request->spot_category, 'document' => $csv_file->getRealPath()], $request->spot_type))->onQueue(env('QUEUE_WORK_NAME', 'default')))) {
            return back()->with('import', true);
        }

        return back()->with('import', false);
    }

    public function getLog(ImportLogRequest $request)
    {
        switch ($request->type) {
            case 'event':
                return SpotsImportCsv::getLog(SpotsImportCsv::EVENT);
            case 'todo':
                return SpotsImportCsv::getLog(SpotsImportCsv::TODO);
            case 'food':
                return SpotsImportCsv::getLog(SpotsImportCsv::FOOD);
            case 'shelter':
                return SpotsImportCsv::getLog(SpotsImportCsv::SHELTER);
        }
    }

    public function deleteLog(ImportLogRequest $request)
    {
        $result = false;
        switch ($request->type) {
            case 'event':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::EVENT);
                break;
            case 'todo':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::TODO);
                break;
            case 'food':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::FOOD);
                break;
            case 'shelter':
                $result = SpotsImportCsv::removeLog(SpotsImportCsv::SHELTER);
                break;
        }

        return back()->with('log_delete', $result);
    }
}
