<?php

namespace App\Http\Requests\Following;

use App\Http\Requests\Request;
use App\Services\Privacy;

class ShowFollowersRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Privacy $privacy
     * @return bool
     */
    public function authorize(Privacy $privacy)
    {
        $target = $this->route('users');
        return $privacy->hasPermission($target, $target->privacy_followers);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
