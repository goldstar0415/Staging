<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Sanitizers\UrlSanitizer;
use App\Http\Requests\Request;

class SpotRequest extends Request
{
    use UrlSanitizer;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('spots')->user_id === $this->user()->id or $this->user()->hasRole('admin');
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
            'description' => 'string|max:5000',
            'start_date' => 'date_format:Y-m-d H:i:s',
            'end_date' => 'required_with:start_date|date_format:Y-m-d H:i:s',
            'locations' => 'array|count_max:20',
            'videos' => 'array|count_max:5',
            'web_sites' => 'array|count_max:5',
            'spot_type_category_id' => 'required_without:is_facebook_import|exists:spot_type_categories,id',
            'tags' => 'array|count_max:7',
            'files' => 'array|count_max:10',
            'is_private' => 'required_without:is_facebook_import|boolean',
            'is_facebook_import' => 'required_without_all:is_private,spot_type_category_id|boolean'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules(
            'locations',
            [
                'address' => 'string|max:255',
                'location.lat' => 'latitude',
                'location.lng' => 'longitude'
            ]
        ));
        $rules = array_merge($rules, $this->arrayFieldRules('videos', 'string|max:255'));
        $rules = array_merge($rules, $this->arrayFieldRules('web_sites', 'url'));
        $rules = array_merge($rules, $this->arrayFieldRules('files', 'image|max:5000', true));
        $rules = array_merge($rules, $this->arrayFieldRules('tags', 'string|max:64'));

        return $rules;
    }
}
