<?php

namespace App\Http\Requests\Spot;

class SpotStoreRequest extends SpotRequest
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
