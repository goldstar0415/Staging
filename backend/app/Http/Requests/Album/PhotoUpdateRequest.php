<?php

namespace App\Http\Requests\Album;

class PhotoUpdateRequest extends AlbumRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'title' => 'required|max:128',
            'is_private' => 'boolean'
        ];
        $rules = array_merge($rules, $this->arrayFieldRules('files', 'image|max:5000', true));
        $rules = array_merge($rules, $this->arrayFieldRules('deleted_ids', 'integer'));
        return $rules;
    }
}
