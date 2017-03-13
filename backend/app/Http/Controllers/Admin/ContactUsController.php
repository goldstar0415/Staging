<?php

namespace App\Http\Controllers\Admin;

use App\ContactUs;
use App\Http\Requests\Admin\SearchRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.contact_us.index')->with('contacts', ContactUs::query()->paginate());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ContactUs  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy($contact)
    {
        $contact->delete();

        return back();
    }

    public function search(SearchRequest $request)
    {
        return view('admin.contact_us.index')->with('contacts', ContactUs::search($request->search_text)->paginate());
    }
}
