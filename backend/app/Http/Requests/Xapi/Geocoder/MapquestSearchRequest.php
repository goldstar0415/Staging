<?php

namespace App\Http\Requests\Xapi\Geocoder;

use App\Http\Requests\Request;

class MapquestSearchRequest extends Request
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
            'addressdetails' => 'numeric',
            'limit' => 'numeric',
            'q' => 'required|string',
        ];

        return $rules;
    }
}
