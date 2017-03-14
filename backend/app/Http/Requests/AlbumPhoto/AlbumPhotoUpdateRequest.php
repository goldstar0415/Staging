<?php

namespace App\Http\Requests\AlbumPhoto;

class AlbumPhotoUpdateRequest extends AlbumPhotoRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'address' => 'string|max:255',
            'location.lat' => 'latitude',
            'location.lng' => 'longitude'
        ];

        return $rules;
    }
}
