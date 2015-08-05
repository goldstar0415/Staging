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
        return $this->route('spots')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [//TODO: cover validation
            'title' => 'required|string|max:255',
            'description' => 'string|max:255',
            'start_date' => 'date',
            'end_date' => 'date',
            'locations' => 'array|count:20',
            'videos' => 'array|count:5',
            'web_sites' => 'array|count:5',
            'spot_type_category_id' => 'required|exists:spot_type_categories,id',
            'tags' => 'array|count:7',
            'files' => 'array|count:10'
        ];


        foreach ($this->input('locations') as $key => $location) {
            $rules['locations.' . $key . '.address'] = 'string|max:255';
            $rules['locations.' . $key . '.location.lat'] = 'numeric';
            $rules['locations.' . $key . '.location.lng'] = 'numeric';
        }

        if ($this->has('videos')) {
            foreach ($this->input('videos') as $key => $location) {
                $rules['videos.' . $key] = 'string|max:255';
            }
        }

        if ($this->has('videos')) {
            $rules = array_merge($rules, $this->arrayFieldRules('web_sites', 'url', false));
        }

        if ($this->hasFile('files')) {
            $rules = array_merge($rules, $this->arrayFieldRules('files', 'image|max:5000'));
        }

        return $rules;
    }
}
