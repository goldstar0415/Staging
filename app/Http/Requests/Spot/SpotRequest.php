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
        $rules = [
            'cover' => 'image|max:5000',
            'title' => 'required|string|max:255',
            'description' => 'string|max:255',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'end_date' => 'date_format:Y-m-d H:i:s',
            'locations' => 'array|count_max:20',
            'videos' => 'array|count_max:5',
            'web_sites' => 'array|count_max:5',
            'spot_type_category_id' => 'required|exists:spot_type_categories,id',
            'tags' => 'array|count_max:7',
            'files' => 'array|count_max:10'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules(
            'locations',
            [
                'address' => 'string|max:255',
                'location.lat' => 'numeric',
                'location.lng' => 'numeric'
            ]
        ));
        $rules = array_merge($rules, $this->arrayFieldRules('videos', 'string|max:255'));
        $rules = array_merge($rules, $this->arrayFieldRules('web_sites', 'url'));
        $rules = array_merge($rules, $this->arrayFieldRules('files', 'image|max:5000', true));

        return $rules;
    }
}
