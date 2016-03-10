<?php

namespace App\Http\Requests\Map;

use App\Http\Requests\Request;

class SpotsSearchRequest extends Request
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
            'type' => 'string',
            'search_text' => 'string',
            'filter.start_date' => 'date_format:Y-m-d',
            'filter.end_date' => 'date_format:Y-m-d',
            'category_ids' => 'array',
            'tags' => 'array',
            'rating' => 'integer'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('category_ids', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('tags', 'string'));

        return $rules;
    }
}
