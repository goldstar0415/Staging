<?php

namespace App\Services;

use Illuminate\Http\Request;

class Attachments
{
    protected $request;

    /**
     * Attachments constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function make($model)
    {
        if ($this->request->has('attachments.album_photos')) {
            $model->albumPhotos()->sync($this->request->input('attachments.album_photos'));
        }
        if ($this->request->has('attachments.spots')) {
            $model->spots()->sync($this->request->input('attachments.spots'));
        }
        if ($this->request->has('attachments.areas')) {
            $model->areas()->sync($this->request->input('attachments.areas'));
        }
    }
}