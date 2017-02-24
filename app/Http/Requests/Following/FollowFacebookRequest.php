<?php

namespace App\Http\Requests\Following;

use App\Http\Requests\Request;
use Log;

/**
 * Class FollowFacebookRequest
 * @package App\Http\Requests\Following
 * @property $ids array
 */
class FollowFacebookRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('users')->id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
