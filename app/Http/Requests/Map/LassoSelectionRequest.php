<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;
use App\Http\Requests\Map\SelectionFilterRulesTrait;

class LassoSelectionRequest extends Request
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
            'vertices' => 'required|array|min:3',
        ];

        $rules = array_merge($rules, $this->getFilterRules());
        $rules = array_merge($rules, $this->arrayFieldRules('vertices', 'required|geopoint'));

        return $rules;
    }

    /**
     * generate a LINESTRING for PostGIS
     * note: this will close the LINESTRING if not already
     *
     * @return string
     */
    public function getClosedLineString()
    {
        // todo: if these dont exist throw runtimeexception
        $path = [];

        foreach($this->input('vertices') as $item) {
            list($lat, $lng) = explode(",", $item);
            $path[] = trim("{$lng} {$lat}");
        }

        $total_vertices = count($path);
        if($total_vertices > 0) {
            // ensure it's closed
            if($path[0] != $path[$total_vertices - 1]) {
                $path[] = $path[0];
            }
        }

        return implode(",", $path);
    }
}
