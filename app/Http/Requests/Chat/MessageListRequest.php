<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\Request;

class MessageListRequest extends Request
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
            'user_id' => 'required|integer',
            'page' => 'required|integer',
            'limit' => 'integer'
        ];
    }
}
