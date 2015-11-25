<?php

namespace App\Http\Requests\Spot;

use App\Http\Requests\Request;

class SpotOwnerRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return is_null($this->route('spots')->user_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'spot_id' => 'integer|exists:spots,id',
            'name' => 'string|max:128',
            'email' => 'string|max:128',
            'phone' => 'string|max:128',
            'address' => 'string|max:255',
            'url' => 'string|max:255'
        ];
    }
}
