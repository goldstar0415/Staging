<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SpotsBulkUpdateRequest extends Request
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
            'location.lat' => 'required_with:location.lng|latitude',
            'location.lng' => 'required_with:location.lat|longitude',
            'address' => 'required_with:location.lat,location.lng|string',
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d',
            'users' => 'integer|exists:users,id',
            'category' => 'integer|exists:spot_type_categories,id'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('spots', 'integer'));

        return $rules;
    }
}
