<?php

namespace App\Extensions;

use Illuminate\Validation\Validator;

/**
 * Class Validations
 * Custom validation rules
 * @package App\Extensions
 */
class Validations extends Validator
{
    /**
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateCountMax($attribute, $value, $parameters)
    {
        return count($value) <= $parameters[0];
    }

    public function validateCount($attribute, $value, $parameters)
    {
        return count($value) == $parameters[0];
    }

    public function validateCountMin($attribute, $value, $parameters)
    {
        return count($value) >= $parameters[0];
    }

    public function validateRemoteImage($attribute, $value, $parameters)
    {
        return (bool)@getimagesize($value);
    }

    public function validateLatitude($attribute, $value, $parameters)
    {
        return is_numeric($value) && $value >= -90 && $value <= 90;
    }

    public function validateLongitude($attribute, $value, $parameters)
    {
        return is_numeric($value) && $value >= -180 && $value <= 180;
    }

    public function validateGeopoint($attribute, $value, $parameters)
    {
        $latlng = explode(",", $value);
        if(count($latlng) != 2) {
            return false;
        }

        list($lat, $lng) = $latlng;

        return is_numeric($lat) && $lat >= -90 && $lat <= 90
            && is_numeric($lng) && $lng >= -180 && $lng <= 180;
    }
}
