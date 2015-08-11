<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;
use App\SpotType;

class SpotCategoriesRequest extends Request
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
        $types = implode(',', array_flatten(SpotType::all('name')->toArray()));
        return [
            'type' => 'in:' . $types
        ];
    }
}
