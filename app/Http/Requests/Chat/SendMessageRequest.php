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
            'message' => 'required|string|max:5000',
            'attachments.photos' => 'array|count:10',
            'attachments.spots' => 'array|count:10',
            'attachments.areas' => 'array|count:10'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.photos', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.spots', 'integer'));
        $rules = array_merge($rules, $this->arrayFieldRules('attachments.areas', 'integer'));

        return $rules;
    }
}
