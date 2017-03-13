<?php

namespace App\Http\Requests\Geocoder;

use App\Http\Requests\Request;

class MapquestReverseRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        $rules = [
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ];

        return $rules;
    }
}
