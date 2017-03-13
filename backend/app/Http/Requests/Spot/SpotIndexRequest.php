<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;

class SpotIndexRequest extends Request
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
            'user_id' => 'integer',
            'page' => 'integer',
            'limit' => 'integer'
        ];
    }
}
