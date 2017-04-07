<?php

namespace App\Http\Requests\Friend;

class UpdateFriendRequest extends FriendRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'avatar' => 'image|max:5000',
            'first_name' => 'max:64',
            'last_name' => 'max:64',
            'email' => 'email|max:128',
            'phone' => 'string|max:24',
            'birth_date' => 'date_format:Y-m-d',
            'note' => 'string|max:255',
            'address' => 'string|max:255',
            'location.lat' => 'latitude',
            'location.lng' => 'longitude'
        ];
    }
}
