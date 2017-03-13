<?php

namespace App\Http\Requests\Calendar;

use App\Http\Requests\Request;

class AddToCalendarRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $spot = $this->route('spots');

        return $this->user()->calendarSpots()->find($spot->id) === null
            and $spot->type === 'event';
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
