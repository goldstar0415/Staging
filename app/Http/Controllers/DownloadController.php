<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

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
        $link = $request->get('link');
//        if (!ctype_alnum($link)) {
//            abort(403, 'Forbidden');
//        }
        $path = storage_path('app/upload/') . $link;
        return response()->download($path);
    }
}
