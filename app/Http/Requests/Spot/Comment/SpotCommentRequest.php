<?php

namespace App\Http\Requests\Spot\Comment;

use App\Http\Requests\Request;
use App\Services\Attachments;

class SpotCommentRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('comments')->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = array_merge(
            ['message' => [
                'required_without_all:attachments.album_photos,attachments.spots,attachments.areas',
                'string',
                'max:5000'
            ]],
            Attachments::$rules,
            $this->arrayFieldRules('attachments.album_photos', 'integer'),
            $this->arrayFieldRules('attachments.spots', 'integer'),
            $this->arrayFieldRules('attachments.areas', 'integer')
        );

        return $rules;
    }
}
