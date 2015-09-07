<?php

namespace App\Extensions;

use Illuminate\Validation\Validator;

class Validations extends Validator
{
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
