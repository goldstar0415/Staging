<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\Request;

class SendMessageRequest extends Request
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
                'count:10'
            ],
            'attachments.spots' => [
                'required_without_all:message,attachments.album_photos,attachments.areas',
                'array',
                'count:10'
            ],
            'attachments.areas' => [
                'required_without_all:message,attachments.album_photos,attachments.spots',
                'array',
                'count:10'
            ]
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.album_photos', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.spots', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.areas', 'integer'));

        return $rules;
    }
}
