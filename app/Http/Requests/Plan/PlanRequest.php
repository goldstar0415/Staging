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
            'start_date' => 'date',
            'end_date' => 'date',
            'address' => 'required|string|max:255',
            'location.lat' => 'required|numeric',
            'location.lng' => 'required|numeric',
            'activities' => 'array',
            'spots' => 'array'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules(
            'activities',
            [
                'title' => 'required|string|max:255',
                'activity_category_id' => 'required|exists:activity_categories,id',
                'description' => 'string|max:255',
                'start_date' => 'date',
                'end_date' => 'date',
                'address' => 'string|max:255',
                'location.lat' => 'numeric',
                'location.lng' => 'numeric'
            ]
        ));
        $rules = array_merge($rules, $this->arrayFieldRules('spots', 'integer'));

        return $rules;
    }
}
