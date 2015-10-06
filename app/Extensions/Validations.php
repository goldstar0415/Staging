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
}
