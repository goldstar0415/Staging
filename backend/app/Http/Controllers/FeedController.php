<?php

namespace App\Http\Controllers;

use App\Feed;
use App\Http\Requests\PaginateRequest;

use App\Http\Requests;

/**
 * Class FeedController
 * @package App\Http\Controllers
 *
 * Feed controller
 */
class FeedController extends Controller
{
    /**
     * Display a listing of the feeds.
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
