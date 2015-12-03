<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SpotImportRequest extends Request
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
            'spot_type' => 'required|integer|exists:spot_types,id',
            'spot_category' => 'required|integer|exists:spot_type_categories,id',
            'document' => 'required|mimes:txt',
        ];
    }
}
