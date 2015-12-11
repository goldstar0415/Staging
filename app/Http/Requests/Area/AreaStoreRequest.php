<?php

namespace App\Http\Requests\Area;

use App\Http\Requests\Request;

class AreaStoreRequest extends Request
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
            'cover' => 'required|image',
            'title' => 'required|string|max:255',
            'description' => 'string|max:255',
            'waypoints' => 'array|required_without:data',
            'data' => 'required_without:waypoints',
            'zoom' => 'integer|between:1,32'
        ];
    }
}
