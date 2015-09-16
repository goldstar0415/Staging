<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    protected function arrayFieldRules($field, $rules, $files = false)
    {
        $result = [];
        $nbr = -1;
        if ($files and $this->hasFile($field)) {
            $nbr = count($this->file($field)) -1;
        } elseif (!$files and $this->has($field)) {
            $nbr = count($this->input($field)) -1;
        }

        if ($nbr != -1) {
            foreach (range(0, $nbr) as $index) {
                if (is_array($rules)) {
                    foreach ($rules as $key => $rule) {
                        $result[$field . '.' . $index . '.' . $key] = $rule;
                    }
                } else {
                    $result[$field . '.' . $index] = $rules;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    final protected function getValidatorInstance()
    {
        if (method_exists($this, 'sanitize')) {
            $this->replace($this->container->call([$this, 'sanitize'], ['data' => $this->all()]));
        }

        return parent::getValidatorInstance();
    }
}
