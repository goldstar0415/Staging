<?php


namespace App\Extensions;

use Carbon\Carbon;

/**
 * Trait StartEndDatesTrait
 * Use it for easy set start/end date of the model
 * @package App\Extensions
 */
trait StartEndDatesTrait
{
    /**
     * Set start date
     * @param Carbon|string $value
     */
    public function setStartDateAttribute($value)
    {
        if (!$value instanceof Carbon) {
            $this->attributes['start_date'] = Carbon::createFromFormat($this->getDateFormat(), $value);
        } else {
            $this->attributes['start_date'] = $value;
        }
    }

    /**
     * Set end date
     * @param Carbon|string $value
     */
    public function setEndDateAttribute($value)
    {
        if (!$value instanceof Carbon) {
            $this->attributes['end_date'] = Carbon::createFromFormat($this->getDateFormat(), $value);
        } else {
            $this->attributes['end_date'] = $value;
        }
    }
}
