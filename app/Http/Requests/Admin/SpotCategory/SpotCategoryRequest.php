<?php

namespace App\Http\Requests\Admin\SpotCategory;

use App\Http\Requests\Request;

class SpotCategoryRequest extends Request
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
            'name' => 'required|max:64|unique:spot_type_categories',
            'display_name' => 'required|max:64',
            'spot_type_id' => 'required|integer|exists:spot_types,id',
            'icon' => 'image'
        ];
    }

    public function sanitize($input)
    {
        $input['name'] = str_slug($input['display_name']);

        return $input;
    }
}
