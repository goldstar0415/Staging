<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;

class TextualSearchRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
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
            'query' => 'required|string|min:3',
            'lat'   => 'latitude',
            'lng'   => 'longitude',
        ];

        return $rules;
    }
    
    /**
     * generates a POINT to be used by PostGIS
     *
     * @return string
     */
    public function getPoint()
    {
        // todo: if these dont exist throw runtimeexception
        $lat = (double) $this->input('lat');
        $lng = (double) $this->input('lng');

        return "{$lng} {$lat}";
    }
}
