<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\Request;
use App\Services\Attachments;

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
            ]
        ];
        $rules = array_merge(
            $rules,
            Attachments::$rules,
            $this->arrayFieldRules('attachments.album_photos', 'integer'),
            $this->arrayFieldRules('attachments.spots', 'integer'),
            $this->arrayFieldRules('attachments.areas', 'integer')
        );

        return $rules;
    }
}
