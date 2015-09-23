<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UrlMetaParseRequest extends Request
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
            'links' => 'required|array'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('links', 'url'));

        return $rules;
    }
}
