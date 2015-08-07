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
            'b_boxes' => 'required|array'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules(
            'b_boxes',
            [
                '_northEast.lat' => 'required|numeric',
                '_northEast.lng' => 'required|numeric',
                '_southWest.lat' => 'required|numeric',
                '_southWest.lng' => 'required|numeric'
            ]
        ));

        return $rules;
    }
}
