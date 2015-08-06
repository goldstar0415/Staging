<?php

namespace App\Http\Controllers;

use App\Http\Requests\Map\MapSearchRequest;
use App\SpotPoint;
use Illuminate\Http\Request;

use App\Http\Requests;

class MapController extends Controller
{
    public function getSearch(MapSearchRequest $request)
    {
        return SpotPoint::getInBBoxes($request->get('b_boxes'));
    }
}
