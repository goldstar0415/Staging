<?php

namespace App\Http\Requests\Blog;

class BlogUpdateRequest extends BlogRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules['slug'] = 'alpha_dash';

        return $rules;
    }
}
