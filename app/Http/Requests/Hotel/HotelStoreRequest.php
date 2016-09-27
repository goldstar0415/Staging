<?php

namespace App\Http\Requests\Hotel;

class HotelStoreRequest extends HotelRequest
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
