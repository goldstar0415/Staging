<?php

namespace App\Http\Requests\PhotoComments;

use Illuminate\Contracts\Auth\Guard;

class PhotoCommentStoreRequest extends PhotoCommentsRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Guard $auth
     * @return bool
     */
    public function authorize(Guard $auth)
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
            'body' => 'required|string|max:255'
        ];
    }
}
