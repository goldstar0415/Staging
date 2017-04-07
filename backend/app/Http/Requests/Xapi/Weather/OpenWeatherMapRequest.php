<?php

namespace App\Http\Requests\Xapi\Weather;

use App\Http\Requests\Request;

class OpenWeatherMapRequest extends Request
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
//            'lat' => 'required|latitude',
//            'lng' => 'required|longitude',
            'bbox' => 'required|string',
            'cluster' => 'required|string|in:yes,no',
            'units' => 'required|string|in:imperial,metric',
            'cnt' => 'required|integer|min:1',
        ];

        return $rules;
    }
}
