<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\Request;

class PlanShowRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $plan = $this->route('plans');

        return $plan->invitedUsers()->find($this->user()->id) !== null
        or $plan->user_id == $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
