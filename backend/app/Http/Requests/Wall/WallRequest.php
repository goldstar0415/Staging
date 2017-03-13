<?php

namespace App\Http\Requests\Wall;

use App\Http\Requests\AttachableRequest;
use App\Http\Requests\Request;
use App\Services\Attachments;

class WallRequest extends Request
{
    use AttachableRequest;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user_id = $this->user()->id;
        $wall = $this->route('wall');

        return $wall->sender_id === $user_id or $wall->receiver_id === $user_id or $this->user()->hasRole('admin');
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
                $this->message_rule,
                'string',
                'max:5000'
            ]
        ];

        $rules = $this->attachmentsRules($rules);

        return $rules;
    }
}
