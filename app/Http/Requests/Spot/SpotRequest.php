<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;

class SpotRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
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
        ];

        foreach ($this->input('locations') as $key => $location) {
            $rules['locations.' . $key . '.address'] = 'string|max:255';
            $rules['locations.' . $key . '.location.lat'] = 'numeric';
            $rules['locations.' . $key . '.location.lng'] = 'numeric';
        }

        foreach ($this->input('videos') as $key => $location) {
            $rules['videos.' . $key] = 'string|max:255';
        }

        return $rules;
    }
}
