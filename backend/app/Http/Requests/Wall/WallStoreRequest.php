<?php

namespace App\Http\Requests\Wall;

class WallStoreRequest extends WallRequest
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
