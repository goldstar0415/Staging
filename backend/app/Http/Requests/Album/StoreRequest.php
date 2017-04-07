<?php

namespace App\Http\Requests\Album;

use Illuminate\Contracts\Auth\Guard;

class StoreRequest extends AlbumRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Guard $auth
     * @return bool
     */
    public function authorize(Guard $auth)
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:128',
            'is_private' => 'boolean',
            'files' => 'required',
            'location.lat' => 'latitude',
            'location.lng' => 'longitude'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('files', 'image|max:5000', true));

        return $rules;
    }
}
