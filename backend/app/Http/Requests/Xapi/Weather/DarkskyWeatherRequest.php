<?php

namespace App\Http\Requests\Xapi\Weather;

use App\Http\Requests\Request;

class DarkskyWeatherRequest extends Request
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
            'lat' => 'required|latitude',
            'lng' => 'required|longitude',
            'lang' => 'required|string|size:2',
            'extend' => 'required|string|in:hourly',
            'units' => 'required|string|in:si,us',
        ];

        return $rules;
    }
}
