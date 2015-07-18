<?php

namespace App\Http\Controllers;

use App\Services\Uploader\Download;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class DownloadController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function index(Request $request)
    {
        return Download::getFile($request->input('id'));
    }
}
