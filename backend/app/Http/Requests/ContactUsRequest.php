<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

/**
 * Class ContactUsRequest
 * @package App\Http\Requests
 */
class ContactUsRequest extends Request
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
            'username' => 'required|string|max:128',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000'
        ];
    }
}
