<?php

namespace App\Http\Requests\Plan;

use App\ActivityCategory;

class PlanStoreRequest extends PlanRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        foreach ($this->input('activities') as $activity) {
            if (ActivityCategory::find($activity['activity_category_id']) === null) {
                return false;
            }
        }
        
        return true;
    }
}
