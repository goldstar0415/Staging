<?php

namespace App\Http\Requests\Friend;

class StoreFriendRequest extends UpdateFriendRequest
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
