<?php

namespace App\Http\Requests\Blog;

use App\Http\Requests\AttachableRequest;
use App\Http\Requests\Request;

class BlogCommentRequest extends Request
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
        $rules = $this->attachmentsRules(['body' => [
            $this->message_rule,
            'string',
            'max:5000'
        ]], 'body');

        return $rules;
    }
}
