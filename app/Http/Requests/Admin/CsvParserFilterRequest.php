<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;

class CsvParserFilterRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filter.title' => 'string|max:255',
            'filter.description' => 'string|max:5000',
            'filter.created_at' => 'date_format:Y-m-d',
        ];
    }

    public function sanitize(array $input)
    {
        if (isset($input['filter'])) {
            foreach ($input['filter'] as &$item) {
                $item = trim($item);
            }
        }

        return $input;
    }
}
