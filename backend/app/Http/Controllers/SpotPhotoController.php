<?php

namespace App\Http\Controllers;

use App\Http\Requests\Spot\AddSpotPhotos;
use App\Http\Controllers\Controller;

class SpotPhotoController extends Controller
{
    /**
     * SpotPhotoController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AddSpotPhotos  $request
     * @param  \App\Spot  $spot
     * @return \Illuminate\Http\Response
     */
    public function store(AddSpotPhotos $request, $spot)
    {
        $photos = [];
        foreach ($request->file('files') as $file) {
            $photos[] = $spot->photos()->create([
                'photo' => $file
            ]);
        }

        return ['photos' => $photos];
    }
}
