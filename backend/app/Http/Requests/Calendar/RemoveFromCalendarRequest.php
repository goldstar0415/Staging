<?php

namespace App\Http\Requests\Calendar;

use App\Http\Requests\Request;

class RemoveFromCalendarRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->calendarSpots()->find($this->route('spots')->id) !== null;
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
