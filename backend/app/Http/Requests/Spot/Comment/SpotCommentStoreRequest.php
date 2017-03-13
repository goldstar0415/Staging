<?php

namespace App\Http\Requests\Spot\Comment;

class SpotCommentStoreRequest extends SpotCommentRequest
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
}
