<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;

class CheckAliasRequest extends Request
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
            'alias' => [
                'required',
                'string',
                'max:64',
                'not_in:' . implode(',', User::NOT_ALLOWED_ALIASES),
                'alpha_dash',
                'regex:' . User::$aliasRule,
                'unique:users,alias,' . $this->user()->id
            ]
        ];
    }
}
