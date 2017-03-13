<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Auth\Guard;

/**
 * Class CommentStoreRequest
 * @package App\Http\Requests
 */
class CommentStoreRequest extends CommentsRequest
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
