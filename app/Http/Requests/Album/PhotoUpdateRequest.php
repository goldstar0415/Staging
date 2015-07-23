<?php

namespace App\Http\Requests\Album;

class PhotoUpdateRequest extends AlbumRequest
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
        $rules = [
            'title' => 'required|max:128',
            'is_private' => 'boolean',
            'files' => 'required'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('files', 'image|max:5000'));
        $rules = array_merge($rules, $this->arrayFieldRules('deleted_ids', 'integer'));
        return $rules;
    }
}
