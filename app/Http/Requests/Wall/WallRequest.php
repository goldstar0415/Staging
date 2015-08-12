<?php

namespace App\Http\Requests\Wall;

use App\Http\Requests\Request;

class WallRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('wall')->sender_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'user_id' => 'required|integer',
            'message' => [
                'required_without_all:attachments.album_photos,attachments.spots,attachments.areas',
                'string',
                'max:5000'
            ],
            'attachments.album_photos' => [
                'required_without_all:message,attachments.spots,attachments.areas',
                'array',
                'count_max:10'
            ],
            'attachments.spots' => [
                'required_without_all:message,attachments.album_photos,attachments.areas',
                'array',
                'count_max:10'
            ],
            'attachments.areas' => [
                'required_without_all:message,attachments.album_photos,attachments.spots',
                'array',
                'count_max:10'
            ]
        ];

        $rules = array_merge($rules, $this->arrayFieldRules('attachments.album_photos', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.spots', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.areas', 'integer'));

        return $rules;
    }
}
