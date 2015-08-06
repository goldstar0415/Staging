<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;

class MapSearchRequest extends Request
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
            'b_boxes' => 'required|array|each:_northEast.lat,'
        ];
        if ($this->has('b_boxes')) {
            foreach ($this->input('b_boxes') as $i => $b_box) {
                $rules['b_boxes.' . $i . '._northEast.lat'] = 'required|numeric';
                $rules['b_boxes.' . $i . '._northEast.lng'] = 'required|numeric';
                $rules['b_boxes.' . $i . '._southWest.lat'] = 'required|numeric';
                $rules['b_boxes.' . $i . '._southWest.lng'] = 'required|numeric';
            }

        }
        return $rules;
    }
}
