<?php

namespace App\Http\Requests\Spot\Review;

use App\Http\Requests\Request;

class SpotReviewRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('spots')->votes()->where('user_id', $this->user()->id)->first() === null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'vote' => 'required|integer|between:1,5'
        ];
    }
}
