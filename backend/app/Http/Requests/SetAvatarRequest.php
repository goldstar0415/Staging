<?php

namespace App\Http\Requests;

/**
 * Class SetAvatarRequest
 * @package App\Http\Requests
 */
class SetAvatarRequest extends Request
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
            'avatar' => 'required|image|max:5000'
        ];
    }
}
