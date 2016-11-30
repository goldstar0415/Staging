<?php

namespace App\Http\Requests\Hotel;

use App\Http\Requests\Sanitizers\UrlSanitizer;
use App\Http\Requests\Request;

class HotelRequest extends Request
{
    use UrlSanitizer;
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
            'title' => 'required|string|max:255',
            'description' => 'string|max:5000',
            'locations' => 'array|count_max:20',
        ];

        return $rules;
    }
}
