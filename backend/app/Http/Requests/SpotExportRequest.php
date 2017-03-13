<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

/**
 * Class SpotExportRequest
 * @package App\Http\Requests
 */
class SpotExportRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('spots')->type === 'event';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
