<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;

class SpotReportRequest extends Request
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
        return [
            'reason' => 'required|integer|max:5',
            'text' => 'string|max:512'
        ];
    }
}
