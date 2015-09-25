<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UrlParseRequest extends Request
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
        $rules = ['links' => 'required|array'];

        $rules = $this->arrayFieldRules('links', 'url');

        return $rules;
    }

    public function sanitize($input)
    {
        foreach ($input['links'] as &$link) {
            if (starts_with('//', $link)) {
                $link = substr_replace($link, 'http://', 0, 2);
            }
        }

        return $input;
    }
}
