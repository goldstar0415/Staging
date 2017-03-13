<?php

namespace App\Http\Requests\Plan;

use App\Http\Requests\Request;
use App\User;

class PlanInviteRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $plan_id = $this->input('plan_id');

        if ($this->user()->plans()->find($plan_id) === null) {
            return false;
        }

        foreach ($this->input('users') as $user_id) {
            $user = User::findOrFail($user_id);
            if ($user->invitedPlans()->find($plan_id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'plan_id' => 'required|integer',
            'users' => 'required|array|count_min:1'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('users', 'integer'));

        return $rules;
    }
}
