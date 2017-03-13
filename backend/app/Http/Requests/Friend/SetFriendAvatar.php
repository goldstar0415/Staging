<?php

namespace App\Http\Requests\Friend;

use App\Http\Requests\SetAvatarRequest;

class SetFriendAvatar extends SetAvatarRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->friends()->find($this->route('friends')->id) !== null;
    }
}
