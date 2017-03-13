<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * Generate rules for array fields
     *
     * @param string $field
     * @param array|string $rules
     * @param bool|false $files
     * @return array
     */
    protected function arrayFieldRules($field, $rules, $files = false)
    {
        $result = [];
        $nbr = -1;
        if ($files and $this->hasFile($field)) {
            $nbr = count($this->file($field)) - 1;
        } elseif (!$files and $this->has($field)) {
            $nbr = count($this->input($field)) - 1;
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
     * {@inheritDoc}
     */
    final protected function getValidatorInstance()
    {
        //Check sanitize traits
        foreach (class_uses_recursive(get_called_class()) as $trait) {
            if (ends_with($trait, 'Sanitizer')
                && method_exists(
                    get_called_class(),
                    $method = 'sanitize' . strstr(class_basename($trait), 'Sanitizer', true)
                )
            ) {
                $this->replace(call_user_func([$this, $method], $this->all()));
            }
        }

        // Sanitize input data before validate
        if (method_exists($this, 'sanitize')) {
            $this->replace($this->container->call([$this, 'sanitize'], ['input' => $this->all()]));
        }

        return parent::getValidatorInstance();
    }
}
