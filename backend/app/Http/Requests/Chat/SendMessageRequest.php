<?php

namespace App\Http\Requests\Chat;

use App\Http\Requests\AttachableRequest;
use App\Http\Requests\Request;
use App\Services\Attachments;

class SendMessageRequest extends Request
{
    use AttachableRequest;
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
                'required_without_all:attachments.album_photos,attachments.spots,attachments.areas,attachments.links',
                'string',
                'max:5000'
            ]
        ];

        $rules = $this->attachmentsRules($rules);

        return $rules;
    }
}
