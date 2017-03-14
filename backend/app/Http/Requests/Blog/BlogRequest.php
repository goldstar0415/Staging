<?php

namespace App\Http\Requests\Blog;

use App\Http\Requests\Request;

class BlogRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->route('posts')->user_id === $this->user()->id or $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cover' => 'image',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|max:255',
            'body' => 'required|max:5000',
            'slug' => 'alpha_dash|max:255|unique:blogs',
            'location.lat' => 'latitude',
            'location.lng' => 'longitude',
            'address' => 'required_with:location|string|max:255',
        ];
    }
}
