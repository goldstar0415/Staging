<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\Request;

class PlanRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('plans')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'string|max:255',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'end_date' => 'required_with:start_date|date_format:Y-m-d H:i:s',
            'address' => 'required|string|max:255',
            'location.lat' => 'required|latitude',
            'location.lng' => 'required|longitude',
            'activities' => 'array',
            'spots' => 'array'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules(
            'activities',
            [
                'title' => 'required|string|max:255',
                'activity_category_id' => 'required|exists:activity_categories,id',
                'position' => 'required|integer',
                'description' => 'string|max:255',
                'start_date' => 'date_format:Y-m-d H:i:s',
                'end_date' => 'date_format:Y-m-d H:i:s',
                'address' => 'required_with:location|string|max:255',
                'location.lat' => 'required|latitude',
                'location.lng' => 'required|longitude'
            ]
        ));
        $rules = array_merge($rules, $this->arrayFieldRules('spots', ['id' => 'integer', 'position' => 'integer']));

        return $rules;
    }
}
