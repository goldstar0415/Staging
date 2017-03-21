<?php

namespace App\Http\Requests;

/**
 * Class UserListRequest
 * @package App\Http\Requests
 */
class UserListRequest extends Request
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
            'filter' => 'string',
            'page' => 'integer',
            'limit' => 'integer'
        ];
    }
}
