<?php

namespace App\Http\Controllers\Admin;

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
}
