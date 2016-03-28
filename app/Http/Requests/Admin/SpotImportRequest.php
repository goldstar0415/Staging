<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Jobs\SpotsImport;

class SpotImportRequest extends Request
{
    protected $spot_types = [
        SpotsImport::EVENT,
        SpotsImport::FOOD,
        SpotsImport::SHELTER,
        SpotsImport::TODO
    ];
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
            'spot_type' => 'required|string|in:' . implode(',', $this->spot_types),
            'spot_category' => 'required|integer|exists:spot_type_categories,id',
            'document' => 'required|mimes:txt',
        ];
    }
}
