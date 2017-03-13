<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;

class SpotListRequest extends Request
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
            'ids' => 'array'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('ids', 'integer'));
        
        return $rules;
    }
}
