<?php

namespace App\Http\Requests;

class PhotoCommentStoreRequest extends PhotoCommentsRequest
{
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
