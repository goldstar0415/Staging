<?php

namespace App\Http\Requests\Spot\Comment;

use App\Http\Requests\AttachableRequest;
use App\Http\Requests\Request;
use App\Services\Attachments;

class SpotCommentRequest extends Request
{
    use AttachableRequest;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('comments')->user_id === $this->user()->id or $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = $this->attachmentsRules(['body' => [
            $this->message_rule,
            'string',
            'max:5000'
        ]], 'body');

        return $rules;
    }
}
