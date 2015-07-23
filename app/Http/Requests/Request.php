<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    protected function arrayFieldRules($field, $rule, $files = true)
    {
        $rules = [];
        $nbr = $files ? count($this->file($field)) - 1 : count($this->input($field)) - 1;
        foreach (range(0, $nbr) as $index) {
            $rules[$field . '.' . $index] = $rule;
        }
        return $rules;
    }
}
