<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;
use App\Http\Requests\Map\SelectionFilterRulesTrait;

class RadiusSelectionRequest extends Request
{
    use SelectionFilterRulesTrait;

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
            'lat'    => 'required|latitude',
            'lng'    => 'required|longitude',
            'radius' => 'required|numeric|min:0|max:10000000', // meters
        ];

        $rules = array_merge($rules, $this->getFilterRules());

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
