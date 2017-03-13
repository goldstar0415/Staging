<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class SendMailRequest extends Request
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
            'users' => 'required|array',
            'subject' => 'required|string|max:512',
            'body' => 'required|string'
        ];
        $rules = array_merge($this->arrayFieldRules('users', 'integer'), $rules);;

        return $rules;
    }
}
