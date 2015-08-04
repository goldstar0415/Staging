<?php


namespace App\Extensions;

use Carbon\Carbon;

trait StartEndDatesTrait
{
    public function setStartDateAttribute($value)
    {
        if (!$value instanceof Carbon) {
            $this->attributes['start_date'] = Carbon::createFromFormat($this->getDateFormat(), $value);
        } else {
            $this->attributes['start_date'] = $value;
        }
    }

    public function setEndDateAttribute($value)
    {
        if (!$value instanceof Carbon) {
            $this->attributes['end_date'] = Carbon::createFromFormat($this->getDateFormat(), $value);
        } else {
            $this->attributes['start_date'] = $value;
        }
    }
}
