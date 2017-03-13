<?php

namespace App\Http\Requests\Spot\Review;

class SpotReviewStoreRequest extends SpotReviewRequest
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
}
