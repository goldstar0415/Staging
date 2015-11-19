<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SpotFilterRequest extends Request
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
            'title' => 'string|max:255',
            'description' => 'string|max:5000',
            'address' => 'string|max:255',
            'username' => 'string|max:255',
            'user_email' => 'string|max:255',
            'date' => 'date_format:Y-m-d',
            'created_at' => 'date_format:Y-m-d'
        ];
    }
}
