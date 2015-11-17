<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SearchRequest;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class EmailController extends Controller
{
    /**
     * Display send mail form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.mail-form');
    }

    /**
     * Send mail to chosen users
     *
     * @return \Illuminate\Http\Response
     */
    public function send()
    {
        //
    }

    public function users(SearchRequest $request)
    {
        return User::search($request->search_text)->get(['id', 'first_name', 'last_name']);
    }
}
