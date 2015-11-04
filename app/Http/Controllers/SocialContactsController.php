<?php

namespace App\Http\Controllers;

use App\Services\Social\GoogleClient;
use App\Services\Social\GoogleContacts;
use App\Services\Social\GoogleToken;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SocialContactsController extends Controller
{
    public function google(Request $request, GoogleClient $googleClient)
    {
        return view('google-contacts')->with(
            'contacts',
            new GoogleContacts($googleClient->getContacts()['feed']['entry'], $googleClient->getToken())
        );
    }
}
