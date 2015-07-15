<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\Download;

class DownloadController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Illuminate\Http\Response\Response
     */
	public function index(Request $request)
	{
		return Download::getFile($request->input('id'));
	}

}
