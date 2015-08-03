<?php

namespace App\Extensions;

use Illuminate\Validation\Validator;

class Validations extends Validator
{
    public function validateCount($attribute, $value, $parameters)
    {
        return count($value) <= $parameters[0];
    }
}