<?php

namespace App\Http\Controllers;

use App\Services\Social\GoogleClient;
use App\Services\Social\GoogleContacts;
use App\Services\Social\GoogleToken;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
use Cache;

class SocialContactsController extends Controller
{
    /**
     * @param Request $request
     * @param GoogleClient $googleClient
     * @return $this
     */
    public function google(Request $request, GoogleClient $googleClient)
    {
        $contacts = $googleClient->getContacts();

        if (!isset($contacts['feed']) or !isset($contacts['feed']['entry'])) {
            return abort(204);
        }

        $c = new GoogleContacts($contacts['feed']['entry'], $googleClient->getToken());

        // fixme, the $request->user() is empty, can't use a cached list

//        $key = 'user-google-contacts-'.$request->user()->id;
//        $c = null;
//        if (Cache::has($key)) {
//            Log::debug('Google Contacts from cache');
//            $c = Cache::get($key);
//        } else {
//            Log::debug('google-contacts 0, user id: ' . $request->user());
//            $contacts = $googleClient->getContacts();
//            if (!isset($contacts['feed']) or !isset($contacts['feed']['entry'])) {
//                abort(204);
//            }
//            $c = new GoogleContacts($contacts['feed']['entry'], $googleClient->getToken());
//            Cache::put($key, $c, 60*3);
//        }
        return view('google-contacts')->with('contacts', $c);
    }
}
