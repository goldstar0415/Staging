<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;

class AddSpotPhotos extends Request
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
            'files' => 'required|array|count_min:1'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('files', 'image', true));

        return $rules;
    }
}
