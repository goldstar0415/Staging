<?php

namespace App\Http\Controllers;

use App\Feed;
use App\Http\Requests\PaginateRequest;

use App\Http\Requests;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param PaginateRequest $request
     */
    public function index(PaginateRequest $request)
    {
        return Feed::where('user_id', $request->get(
            'user_id',
            $request->user() ? $request->user()->id : null
        ))->paginate((int) $request->get('limit', 10));
    }
}
