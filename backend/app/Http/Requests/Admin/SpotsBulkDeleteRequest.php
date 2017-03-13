<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SpotsBulkDeleteRequest extends Request
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
            'spots' => 'required|array',
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('spots', 'integer'));

        return $rules;
    }
}
