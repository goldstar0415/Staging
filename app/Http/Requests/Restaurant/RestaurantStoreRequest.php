<?php

namespace App\Http\Requests\Restaurant;

class RestaurantStoreRequest extends RestaurantRequest
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
