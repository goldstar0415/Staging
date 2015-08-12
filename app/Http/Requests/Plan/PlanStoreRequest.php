<?php

namespace App\Http\Requests\Plan;

class PlanStoreRequest extends PlanRequest
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
