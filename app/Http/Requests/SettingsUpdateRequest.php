<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SettingsUpdateRequest extends Request
{
    protected $types = [
        'personal',
        'security',
        'password',
        'privacy',
        'notifications'
    ];

    protected $request_type;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return in_array($this->request_type, $this->types);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $this->request_type = $this->input('type');

        $rules = [];
        switch ($this->getType()) {
            case 'personal':
                $rules = [
                    'first_name' => 'required|max:64',
                    'last_name' => 'required|max:64',
                    'birth_date' => 'date',
                    'sex' => 'boolean',
                    'time_zone' => 'timezone',
                    'description' => 'string|max:255',
                    'address' => 'string|max:255',
                    'location.lat' => 'numeric',
                    'location.lng' => 'numeric'
                ];
                break;
            case 'security':
                $rules = [
                    'email' => 'required|email|max:128|unique:users,email'
                ];
                break;
            case 'password':
                $rules = [
                    'current_password' => 'required',
                    'password' => 'required|min:6|confirmed'
                ];
                break;
            case 'privacy':
                $rules = array_fill_keys(array_keys($this->input()), 'integer|between:1,5');
                break;
            case 'notifications':
                $rules = array_fill_keys(array_keys($this->input()), 'boolean');
                break;
        }

        foreach ($rules as $key => $value) {
            $rules['params.' . $key] = $rules[$key];
            unset($rules[$key]);
        }

        return $rules;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->request_type;
    }
}
