<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;
use App\Http\Requests\Map\SelectionFilterRulesTrait;

class PathSelectionRequest extends Request
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
            'vertices' => 'required|array|min:2',
            'buffer'   => 'required|numeric|min:1|max:100000', // think radius but for a line (meters)
        ];

        $rules = array_merge($rules, $this->getFilterRules());
        $rules = array_merge($rules, $this->arrayFieldRules('vertices', 'required|geopoint'));

        return $rules;
    }

    /**
     * generates a LINESTRING to be used by PostGIS
     *
     * @return string
     */
    public function getLineString()
    {
        // todo: if these dont exist throw runtimeexception
        $path = [];

        foreach($this->input('vertices') as $item) {
            list($lat, $lng) = explode(",", $item);
            $path[] = trim("{$lng} {$lat}");
        }

        return implode(",", $path);
    }
}
